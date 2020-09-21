# Tekstmijn: stafgedeelte
![Logo Tekstmijn](https://tekstmijn.nl/staff/assets/img/mailheader.png)

Dit is het stafgedeelte van het Tekstmijnproject. 
Dit ontsluit de volgende functionaliteiten (en gebruikersrollen):
*   Opdrachten inzien en inzendingen beoordelen (allen);
*   Leerlingen en klassen inzien (docenten);
*   Opdrachten aanmaken en toewijzen (beheerders);
*   Vragenlijsten aanmaken en toewijzen (beheerders);
*   Analyseren van beoordelingen en downloaden van teksten (beheerders);
*   Aanmaken en toewijzen van instellingen, klassen, personeelsleden en leerlingen (beheerders). 

De inzendingen komen voort uit het
[studentgedeelte](https://www.github.com/leonmelein/tekstmijn).

## Systeemvereisten
*   Voldoende vrije schijfruimte (Advies: minimaal 50 GB)
*   PHP 7.0 of hoger
*   MySQL 5.7 of hoger met `SQL_MODE=ANSI_QUOTES` ingeschakeld

## Installatie
1.  Maak op uw server de map _staff_ aan.
2.  Maak in deze map de nieuwe map _config_ aan. Plaats hierin een `.htaccess`-bestand
met `deny from all` als inhoud. Maak vervolgens een `config.ini`-bestand
aan. De inhoud hiervan is als volgt: 
```
[mysql]
server = <Adres van de MySQL Server, meestal: localhost>
database_name = <Databasenaam>
username = <Databasegebruiker>
password = <Wachtwoord databasegebruiker>
```

3.  Plaats de bestanden uit deze repository op de server.
4.  Laad [de benodigde tabellen](https://github.com/leonmelein/tekstmijn_staff/blob/master/database.sql) in via phpMyAdmin of vergelijkbare databasebeheersoftware.

Hierna kan het systeem in gebruik worden genomen door in een browser naar `/staff` op de server te navigeren en in te loggen.

