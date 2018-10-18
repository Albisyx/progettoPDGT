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
	  	      	  info['Nome'] = artist['name'];
	  	      	  info['Followers'] = artist['followers']['total'];
	  	      	  info['Popolarità'] = artist['popularity'];
	              info['Link'] = artist['uri'];

	  	      	  let generi = [];
	  	      	  for(let i = 0; i < artist['genres'].length; i++) 
	  	      	      generi.push(artist['genres'][i]);

	  		      info['Generi'] = generi;
	  		      res.send(info);
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
          res.send(nuoviAlbum);
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
		getArtistFromTrack(req.query.track_name, res);
	else if(req.query.artist != undefined && req.query.track_name != undefined)
		getLyrics(req.query.artist, req.query.track_name, res);
})

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

          response.send(topTracks);
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
            "Tipo album"       : item.album_type,
            "Nome"             : item.name,
            "ID"               : item.id,
            "Artisti"          : artisti,
            "Data di rilascio" : item.release_date,
            "Link"             : item.uri
        });
    })

    return datiAlbum;
};

// funzione ausiliaria per trovare il nome di un'artista, partendo dal nome di una sua canzone
function getArtistFromTrack(trackName, res)
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
          if(data['tracks']['items'].length)
              getLyrics(data['tracks']['items'][0]['artists']['name'], encodeURIComponent(trackName), res);
          else
          	  res.status(404).send({error : 'Artista non trovato partendo da questa canzone'});
      })
      .catch(function(err)
      {
          if(err['statusCode'] == 401)
              res.send(err['error']);
          else
              res.send(err);
      })
}

function getLyrics(artistName, trackName, response)
{
	let url = 'https://api.lyrics.ovh/v1/' + artistName + '/' + trackName;

	rp(url)
	  .then(function(data)
	  {
	  	  if(!data['error'])
	  	  	  response.status(200).send(data['lyrics']);
	  	  else
	  	  	  response.status(404).send({error : 'Lyrics non trovato'});
	  })
	  .catch(function(err)
      {
          response.send(err);
      })
}

app.listen(PORT, function()
{
    console.log('Server in ascolto sulla porta ' + PORT);
});