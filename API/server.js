let express = require('express');
let rp = require('request-promise');
let SpotifyWebApi = require('spotify-web-api-node');
let credenziali = require('./credenziali.js'); // file contenente le credenziali per autenticarsi con spotify
                                               // esso non è presente nella repository
let app = express();

// uso la libreria spotify-web-api-node per ottenere un acces_token
// uso la modalità di autenticazione fornita da spotify che permette di ottenere, 
// da parte di un client, un token per poter eseguire le API
// quindi l'autenticazione è a livello di client, non a livello di utente

let spotifyApi = new SpotifyWebApi({
  clientId: credenziali.client_id, 
  clientSecret: credenziali.client_secret
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
app.get('/top-tracks/:nomeArtista', (req, res) => 
{
    // variabile per effettuare la prima chiamata ed ottenere l'id partendo dal nome
    let artistIDOptions =
    {
      uri: 'https://api.spotify.com/v1/search?q=' + encodeURIComponent(req.params.nomeArtista) +'&type=artist&market=it&limit=1',
      headers: 
          {
              'Authorization': 'Bearer ' + spotifyApi.getAccessToken()
          },
      json: true
    };

    rp(artistIDOptions)
      .then(function(data)
      {
          // mi faccio stampare l'id per vedere se la chiamata ha funzinato
          console.log( 'ID => ' + data['artists']['items'][0]['id']);
          res.end();
      })
      .catch(function(err)
      {
          console.log(err);
          res.send(err);
      });
});  
    
app.listen(3000, function()
  {
      console.log('Server in ascolto sulla porta 3000...');
  });