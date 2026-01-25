## CMS
The project is based on Trickster CMS, a proprietary CMS without documentation.
CMS is organized as a set of packages (cms, homepage, project). Project is a top-priority package, and its files override all others.
In future we will get rid of this unsupported CMS by incorporating its functionality into our project.

## File structure:
- /htdocs/ - mapped to public web root
- /ng-zxart/ - Angular frontend. This convers only a pair of views from whole module yet. see angular.md for more info
- /project/ - project is "zxart" itself, this folder contains all domain-related source code and extends structure of CMS package.
- /project/core/ - legacy services, models, helpers. This should be refactored.
- /project/core/ZxArt/ - modern services with namespaces and intended structure. All new code goes here except when dealing with legacy modules.
- /project/css/ and /project/js/ - legacy frontend assets. they are built on the fly.
- /project/services/ - legacy DI container services. should not be added, only refactored to PHP-DI.
- /project/templates/ - legacy smarty templates.
- /trickster-cms/ - copy of CMS. In dev environment project is linked to this folder. In prod environment it is served from composer.
- ./tests/ - phpunit tests. all new functionality should be covered by tests.