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
      console.log('Token ' + data.body['access_token']);

      // salvo il token almeno lo posso usare nelle future chiamate
      spotifyApi.setAccessToken(data.body['access_token']);
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
        uri: 'https://api.spotify.com/v1/search?q=' + encodeURIComponent(req.params.nomeArtista) +'&type=artist&market=it&limit=1',
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
  	          getArtistTopTracks(data['artists']['items'][0]['id'], res);
  	      })
  	      .catch(function(err)
  	      {
  	          console.log(err);
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

  	      	  let generi = [];
  	      	  for(var i = artist['genres'].length - 1; i >= 0; i--) {
  	      	      generi.push(artist['genres'][i]);
  			      }

  			      info['Generi'] = generi;
  			      res.send(info);
  	      })
  	      .catch(function(err){
              res.send(err);
  	      })
	}
});  


// funzione che crea e restituisce il file json contenente le top tracks dato 
// l'id di un'artista precedentemente ricavato
function getArtistTopTracks(IDArtista, response)
{
    // variabile per effettuare la seconda chiamata alle API di spotify
    let topTracksOptions = 
    {
        uri: 'https://api.spotify.com/v1/artists/' + IDArtista + '/top-tracks?country=it',
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
              tracks : []
          };

          for(item in data['tracks'])
              topTracks.tracks.push(data['tracks'][item]['name']);

          response.send(topTracks);
      })
      .catch(function(err)
      {
          console.log(err);
          response.send(err);
      })
};

app.listen(PORT, function()
  {
      console.log('Server in ascolto sulla porta ' + PORT);
  });