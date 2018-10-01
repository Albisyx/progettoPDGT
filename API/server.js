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

app.get('/', (req, res) => 
{
    res.send('homepage').end();
});  
    
app.listen(3000, function()
  {
      console.log('Server in ascolto sulla porta 3000...');
  });