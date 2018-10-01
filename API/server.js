var express = require('express');
var rp = require('request-promise');
var app = express();

app.get('/', (req, res) => 
{
    res.send('<h1>homepage</h1>');
});  
    
app.listen(3000, function()
  {
      console.log('Server in ascolto sulla porta 3000...');
  });