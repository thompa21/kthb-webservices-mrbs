# MRBS API Version 1
Api mot KTH Bibliotekets system för bokning av Grupprum och Handledning
## Funktioner
### Används främst för att kvittera bokningar med den kod som skapades vid bokningen i applikationen.

https://apps.lib.kth.se/webservices/mrbs/api/v1/entries/confirm/{code}?lang=sv

### Övriga anrop kräver api_key eller JWT-token

https://apps.lib.kth.se/webservices/mrbs/api/v1/entries/4812?api_key={apikey}&lang=sv

https://apps.lib.kth.se/webservices/mrbs/api/v1/entries/4812?token={token}&lang=sv

### Anonyma anrop för visning på t.ex bibliotekets smartsign-skärmar
https://apps.lib.kth.se/webservices/mrbs/api/v1/noauth/entries
