let express = require('express');
let rp = require('request-promise');
let SpotifyWebApi = require('spotify-web-api-node');
let app = express();
const PORT = process.env.PORT || 5000;

// uso la libreria spotify-web-api-node per ottenere un acces_token
// uso la modalità di autenticazione fornita da spotify che permette di ottenere, 
// da parte di un client, un token per poter eseguire le API
// quindi l'autenticazione è a livello di client, non a livello di utente

let spotifyApi = new SpotifyWebApi({
  clientId: process.env.CLIENT_ID, 
  clientSecret: process.env.CLIENT_SECRET
});

// invoco la funzione per ottenere il token
spotifyAuthentication();

// metodo che ritorna le 10 canzoni più popolari di un artista
// per far cio, devo prima ottenere l'id dell'artista partendo dal suo nome
 // devo quindi effettuare due chiamate a due metodi diversi delle API di Spotify
app.get('/artist/:nomeArtista', (req, res) => 
{
    // Variabile per effettuare la prima chiamata ed ottenere l'id partendo dal nome
    // Ogni chiamata deve essere accompagnata da un header che serve ai server di spotify
    // per autenticare il client e permettergli di accedere ai dati richiesti 
    let artistIDOptions =
    {
        uri: 'https://api.spotify.com/v1/search?q=' + encodeURIComponent(req.params.nomeArtista) +'&type=artist&market=IT&limit=1',
        headers: 
            {
                'Authorization': 'Bearer ' + spotifyApi.getAccessToken()
            },
        json: true
    };

    if(req.query.type == 'top-tracks')
    {
      	rp(artistIDOptions)
  	      .then(function(data)
  	      {
              let ID = data['artists']['items'][0]['id'];
              let artistName = data['artists']['items'][0]['name'];
  	          getArtistTopTracks(ID, artistName, res);
  	      })
  	      .catch(function(err)
  	      {
  	          if(err['statusCode'] == 401)
                  res.send(err['error']);
              else
                  res.send(err);
  	      });
	  }
	  else if(req.query.type == 'info')
	  {
		    let info = {};

	  	    rp(artistIDOptions)
	  	      .then(function(data)
	  	      {
	  	      	  let artist = data['artists']['items'][0];
	  	      	  info['nome'] = artist['name'];
	  	      	  info['followers'] = artist['followers']['total'];
	  	      	  info['popolarità'] = artist['popularity'];
	              info['link'] = artist['external_urls']['spotify'];
                info['foto_artista'] = artist['images'][0]['url'];

	  	      	  let generi = [];
	  	      	  for(let i = 0; i < artist['genres'].length; i++) 
	  	      	      generi.push(artist['genres'][i]);

	  		        info['generi'] = generi;
	  		        res.status(200).send(info);
	  	      })
	  	      .catch(function(err)
            {
	              if(err['statusCode'] == 401)
                    res.send(err['error']);
                else
                    res.send(err);
	  	      })
	  }
});  

//metodo che ritorna 5 nuovi album rilasciati nel mercato italiano
app.get('/new-releases', (req, res) =>
{
    let options = 
    {
        uri: 'https://api.spotify.com/v1/browse/new-releases?country=IT&limit=5',
        headers: 
        {
            'Authorization': 'Bearer ' + spotifyApi.getAccessToken()
        },
        json: true
    };

    rp(options)
      .then(function(data)
      {
          let nuoviAlbum = getNewReleases(data['albums']['items']);
          res.status(200).send(nuoviAlbum);
      })
      .catch(function(err)
      {
          if(err['statusCode'] == 401)
              res.send(err['error']);
          else
              res.send(err);
      })
});

// percorso che ritorna il testo di una canzone
// richiede due parametry, ovvero il nome dell'artista e quello della canzone
app.get('/lyrics', (req, res) =>
{
	if(req.query.artist == undefined && req.query.track_name == undefined)
	{
		let error = {
			internal_error : "Nome dell'artista e della relativa canzone mancanti"
		};
		res.status(500).send(error);
	}
	else if(req.query.artist == undefined && req.query.track_name != undefined)
		getInfoFromTrack('a', req.query.track_name, res);
	else if(req.query.artist != undefined && req.query.track_name != undefined)
		getLyrics(req.query.artist, req.query.track_name, res);
});

app.get('/listen/:trackTitle', (req, res) => 
{
	getInfoFromTrack('p', req.params.trackTitle, res);
});

app.get('/refresh-token', (req, res) =>
{
	spotifyAuthentication();
	res.status(200).send({status : "OK"});
});

// funzione che crea e restituisce il file json contenente le top tracks dato 
// l'id di un'artista precedentemente ricavato
function getArtistTopTracks(IDArtista, nomeArtista, response)
{
    // variabile per effettuare la seconda chiamata alle API di spotify
    let topTracksOptions = 
    {
        uri: 'https://api.spotify.com/v1/artists/' + IDArtista + '/top-tracks?country=IT',
        headers:
        {
            'Authorization': 'Bearer ' + spotifyApi.getAccessToken()
        },
        json: true
    };   
    
    rp(topTracksOptions)
      .then(function(data)
      {
          let topTracks = {
              nome_artista : nomeArtista,
              tracks : []
          };

          for(item in data['tracks'])
              topTracks.tracks.push(data['tracks'][item]['name']);

          response.status(200).send(topTracks);
      })
      .catch(function(err)
      {
          if(err['statusCode'] == 401)
              res.send(err['error']);
          else
              res.send(err);
      })
};

//funzione per comporre il JSON che verrà poi restituito come risposta
function getNewReleases(releases)
{
    let datiAlbum = {
        albums: []
    };

    // per ogni album nuovo, prelevo le informazioni più rilevanti
    releases.map(function(item) 
    {        
        // creo un vettore con tutti gli artisti che hanno partecipato all'i-esimo album
        let artisti = [];
        for(let i = 0; i < item.artists.length; i++) 
            artisti.push(item.artists[i].name);

        // popolo l'oggetto finale
        datiAlbum.albums.push(
        { 
            "tipo_album"       : item.album_type,
            "nome"             : item.name,
            "ID"               : item.id,
            "artisti"          : artisti,
            "data_di_rilascio" : item.release_date,
            "link_album"       : item.external_urls.spotify,
            "link_artista"     : item.artists[0].external_urls.spotify,
            "cover_album"      : item.images[0].url
        });
    })

    return datiAlbum;
};

// funzione ausiliaria per trovare il nome di un'artista, partendo dal nome di una sua canzone
// il parametro mod viene usato in quanto la seguente funzione viene invocata in due scopi diversi:
// per mod == 'a' -> la funzione invoca getLyrics passandole come parametro il nome dell'artista della canzone trovata
// per mod ?? 'p' -> la funzione restituisce un JSON con alcune informazioni sulla canzone e in particolare il link per ascoltare 30 secondi
function getInfoFromTrack(mod, trackName, res)
{
	let trackOptions =
    {
        uri: 'https://api.spotify.com/v1/search?q=' + encodeURIComponent(trackName) +'&type=track&market=IT&limit=1',
        headers: 
        {
             'Authorization': 'Bearer ' + spotifyApi.getAccessToken()
        },
        json: true
    };

    rp(trackOptions)
      .then(function(data)
      {
      	  if(data['tracks']['total'] > 0)
      	  {
          	  if(mod == 'a')
                  getLyrics(data['tracks']['items'][0]['artists'][0]['name'], encodeURIComponent(trackName), res);
              else if(mod == 'p')
              {
              	  let track = data['tracks']['items'][0];
              	  let trackInfo = {
              	  	  nome : track.name,
              	  	  artista : track.artists[0].name,
              	  	  album : track.album.name,
              	  	  link_preview : track.preview_url,
              	  	  link_traccia : track.external_urls.spotify,
                      foto_traccia : track.album.images[0].url
              	  };

              	  res.status(200).send(trackInfo);
           	  }
          }
          else
          	  res.send({error : {status : 404, message : 'Canzone non trovata'}});
      })
      .catch(function(err)
      {
          if(err['statusCode'] == 401)
              res.send(err['error']);
          else
              res.send(err);
      })
}

//funzione che sfrutta un'API esterna per ricevere il testo di una determinata canzone
// partendo dal nome dell'artista e quello della traccia in se
function getLyrics(artistName, trackName, response)
{
	let lyricsOptions =
	{
		  url: 'https://api.lyrics.ovh/v1/' + artistName + '/' + trackName,
		  json: true
	};

	rp(lyricsOptions)
	  .then(function(data)
	  {
	  	  let result = {
	  	  	  artist : artistName,
	  	  	  lyrics : data['lyrics']
	  	  };
	  	  response.status(200).send(result);
	  })
	  .catch(function(err)
      {
      	if(err['statusCode'] == 404)
          	response.send({error : {status : 404, message : 'Lyrics non trovata'}});
        else
        	response.send(err);
      })
}

// funzione che fa una richiesta HTTP ai server di Spotify per ottenere il token utile ad accedere ai dati che questa
// API mette a disposizione
function spotifyAuthentication()
{
	spotifyApi.clientCredentialsGrant()
  	  .then(function(data) 
  	  {
      	  // salvo il token, almeno lo posso usare nelle future chiamate
      	  spotifyApi.setAccessToken(data.body['access_token']);
      	  console.log('Token ottenuto. scadrà in ' + data.body['expires_in']);
  	  },
  	  function(err) 
      {
          console.log(err);
      });
}

app.listen(PORT, function()
{
    console.log('Server in ascolto sulla porta ' + PORT);
});