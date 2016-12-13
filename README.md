# ICTU---Digitale-Overheid-Plugin-Releasekalender
Plugin voor het importeren en tonen van releasekalenderinformatie op digitaleoverheid.nl.
Deze plugin wordt gebruikt op [digitaleoverheid.nl](https://digitaleoverheid.nl). Opdrachtgever is ICTU (ICT Uitvoeringsorganisatie). 

Ontwikkelaars zijn:

* Paul van Buuren ([WBVB Rotterdam](https://wbvb.nl)) 
* Marcel Bootsman ([nostromo.nl](https://nostromo.nl)) 


## Rubber ducking
De releasekalender toont gegevens uit de releasekalender database van [invoegen].
Deze gegevens worden via CRON-jobs opgehaald. Er is een knop om deze import handmatig te starten. De gegevens worden in aparte tabellen weggeschreven.

## Front-end

### Pagina-templates

Twee verschillende pagina templates zijn nodig:
#### 1.	Releasekalender-core pagina template
*rhswp-rk-coretemplate.php*  
Core functionaliteit: een pagina-template opzetten; deze pagina template regelt de core van alle functionaliteit.  
Het content-veld op deze pagina wordt gebruikt voor de inleiding

Extra benodigde velden bij het invoeren van de content op deze pagina:

* inleidingstekst in de download van de totale tabel
* linktekst voor de download van de totale tabel

Deze pagina is verantwoordelijk voor de verdere afhandeling van de releasekalender door rewriterules:

``<pagina-url>/voorziening/<voorziening-slug>``  

zo ongeveer ook als de huidige situatie:  

``digitaleoverheid.nl/onderwerpen/voortgang-en-planning/releasekalender/bouwsteen/basisregistratie-grootschalige-topografie-bgt``

#### 2.	Dossier releasekalender-pagina template
*rhswp-rk-dossiertemplate.php*  
gebruikt door de pagina in een dossier die een onderdeel van de planning kan tonen. Eventuele onderdelen hierin kun je kiezen via de lijst met bouwstenen.

Eventuele links in de kalenderfunctionaliteit zelf verwijzen naar de bovenstaande core-pagina. In de dossier-context blijven lijkt me geen goed idee, want reuze-ingewikkeld


## Back-end

API instellingen
-	er moet een knop zijn voor het handmatig starten van de import ('ververs data')
  

Groepering
-	voorzieningen kunnen groeperen. Er kunnen subgroepen worden aangemaakt. De bestaande groepering is:

* gegevens
	* basisregistraties (subgroep van gegevens)
* dienstverlening
* identificatie
* interconnectiviteit

Van deze groepen moet de naam gewijzigd kunnen worden; moet een hoofd-/subgroep zijn

Per bouwsteen (nehee: voorzieningen) moet de groep aangewezen kunnen worden

## Conventies, folders & files

bestandsnamen allemaal in onderkast en dashes.
dus niet zo:  
``Ikben_een_bestandsnaam.php``  
maar zo:  
``ik-ben-een-bestandsnaam.php``  

*prefix:*  ``rijksreleasekalender_`` (rijkshuisstijl releasekalender)

### Structuur

```shell
plugins/rhswp-releasekalender/                      # → Folder met alle plugin-bestanden
├── admin/                                          # → alle benodigdheden voor admin
│   ├── css/                                        # → CSS-bestanden
│   ├── js/                                         # → JavaScript voor admin
│   └── partials/                                   # 
├────── class-rijksreleasekalender-admin.php        # → admin core
├── includes/                                       # → evt. includes
│   ├── class-rijksreleasekalender-activator.php    #
│   ├── class-rijksreleasekalender-deactivator.php  #
│   ├── class-rijksreleasekalender-i18n.php         #
│   ├── class-rijksreleasekalender-loader.php       #
│   └── class-rijksreleasekalender.php              #
├── languages/                                      # → Voor vertalingen (POT, PO, MO)
├── public/                                         # → CSS / images / JS
│   ├── css/                                        # → CSS-bestanden (resultante van LESS)
│   ├── images/                                     # → Plaatjes (jpg, gif, png, svg)
│   ├── less/                                       # → LESS source files (compileren naar ../css)
│   ├── js/                                         # → JavaScript bronbestanden
│   │  └── min/                                     # → Minified JavaScript
│   ├── partials/                                   #
│   └── class-rijksreleasekalender-public.php       #
├── index.php                                       #
└── rhswp-releasekalender.php                       # → Plugin core

```
  
## Versies

* 1.0.1 - added some functionality for public pages
* 1.0.0 - File and folder structure

## Overige opmerkingen
Bouwstenen mogen geen bouwstenen heten maar voorzieningen.

