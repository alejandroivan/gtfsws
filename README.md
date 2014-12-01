GTFS RESTful WebService
=======================

First of all, what is GTFS? It's the General Transport Feed Specification, a standard for managing public transportation data for a city or defined area. In short words, a GTFS repository is a .zip file containing lots of .txt files as comma-separated-values (CSV). More info: https://developers.google.com/transit/gtfs/

This project will try to create a WebService which will allow multiple users to manage multiple GTFS repositories in the same system, based on a RESTful point of view about web services, using Chris Kacerguis' REST implementation for CodeIgniter (https://github.com/chriskacerguis/codeigniter-restserver).

Requirements:
- The same as CodeIgniter (http://www.codeigniter.com/)
- MySQL 5+ database
- Some web server permissions for importing .zip files containing GTFS info (depending on the repository: lots of RAM and processing cycles; depending on the web server: permissions to upload files; etc.)

What this project aims for:
- Avoiding to implement and pay different GTFS editors and servers for each city, when you should be able to manage public transportation data in an unique system.
- Generating a web service, which any software (view) should be able to connect, gather, update or delete data from the database.
- Being able to import data from GTFS .zip files to the database and vice-versa.
- Being free software and easy to understand, so people could take the code and generate new versions from it (even new cool software!) without too much effort.

For who is this useful?
- Authorities who would want to keep public transportation data (schedules, agencies, etc.) in a single system and then being able to export it for public knowledge.
- Any person/company who would like to manage GTFS repositories and use them for any reason.
- Companies/people that want to start digging and learning about GTFS repositories.
- Companies/people that want to start consulting for another companies using public transportation information.

License?

Basically GPLv3 (see LICENSE.md). **You will not, however, be able to sell derivative software. Even more, derivative software will need to be FULLY GPL LICENSED, that means software which mixes any part of the code of this project with propietary code will not be tolerated. All diagrams, SQL sentences and other documentation included with this project is also restricted this way** (you can install this software on propietary servers, but you can't get money for that). You can, however, give support and get paid for it without problems after the system is functional (remember that modifications are GPL licensed!).
