# Music API #

## Progetto Piattaforme Digitali per la Gestione del Territorio ##

### Alunni ###
+ [Lorenzo Genghini](https://github.com/Lorenzo1997)
+ [Alberto Spadoni](https://github.com/Albisyx)

---

### Obbiettivi ###
Il progetto **Music API**, si pone i seguenti obbiettivi:
1. Partendo da un determinato artista, fornisce alcune informazioni e le 10 canzoni più popolari
2. Permette di conoscere gli album usciti recentemente
3. Da la possibilità di cercare il testo delle canzoni desiderate
4. Consente di ascoltare della musica

---

### Componenti ###
Music API è composto da 2 parti:
+ La vera e porpria API, sviluppata in **NodeJS + Express**
+ Un client realizzato sottofroma di un **bot per Telegra**, che rende semplice l'interfacciamento fra utente e API

---

## Descrizione ##

L'API mette a disposizione alcuni metodi, di tipo GET, che sfrutttano le API di Spotify e quelle di Lirycs.ovh per effetuare le operazioni sopra citate.

Per raggiungere gli obbiettivi 1, 2 e 4, si prelevano le informazini necessarie da Spotify. Per fare ciò è necessario autenticarsi utilizzando il metodo Client Credentials messo a disposizione da Spotify stesso. In breve, il metodo autentica il client fornendogli un token che potra essere utilizzato per accedere hai data base di Spotify, tramite opportune richieste HTTP

Passi per l'ottenimetno delle informazioni da Spotify
Si effettua la richiesta HTTP di interesse, inviando un'opportuno header contenente il token di accesso precedentemente ottenuto.
A questo punto, se la richiesta è nadata a buon fine, verranno restituite, in formato JSON, le informazioni corrispondenti alla richieste e ai parametri scelti.
Ora viene composto il JSON contentente le informazioni più rilevanti, che poi verra successivamente restituito nel corpo della risposta.

Per quato riguarda il terzo obbiettivo, ci si è appoggiati all'API di Lirycs.ovh. Queste ultime non hanno bisogno di alcuna autenticazione, quindi basta effettuare una semplice richiesta HTTP

Passi per l'ottenimento del testo tramite Lirycs.ovh:
Qesta API richiede come parametri sia il nome dell'artista che quello della canzone. Music API permette di utilizzarla in due modi:
+ Forneddo solo il nome della conzone
+ Inserendo sia il nome della cnzone che quello dell'artista

Nel primo caso occore ricavare il nome dell'artista per rispettare la sintassi di Lirycs.ovh. Ciò viene effettuato con l'ausilio delle api di Spotify.