Verschwende keine Rechenleistung und keinen Speicherplatz mit Thumbnails!  
Mit diesem Plugin können Sie auf dynamische Thumbnails zurückgreifen. Außerdem stellt dieses Plugin die Funktion von LazyLoading mit automatisch generierten Sizes für Thumbnails zu Verfügung.  
Jeder Artikel, jedes Bild in der Einkaufswelten hat berechtigterweise Thumbnails. Diese werden standardmäßig
automatisch beim Upload erzeugt und gespeichert.  
An diesem Punkt greift dieses Plugin ein und stellt die Funktion zur Verfügung, dass keine Thumbnails mehr erstellt
werden müssen.  
Die Thumbnails werden dann in Echtzeit beim Besuch durch einen externen Dienst erzeugt und ausgeliefert.

## Vorteile vom Sparen der Thumbnailerzeugung:
- Sparen von Speicherplatz
- Schnellerer Upload von Bildern
- Entlastung des Servers
- Schnellere Backups durch weniger Dateien

## Hinzufügen weiterer Thumbnail-Größen:
- im Order der Medienverwaltung neue Größe hinterlegen
- dann den Befehl `bin/console media:generate-thumbnails` auf der Konsole ausführen, damit die Thumbnails für alle Bilder in der Datenbank aktualisiert werden
- Shop-Cache leeren

## Welchen Dienst verwende ich nun für die Thumbnails:
Beachten Sie, dass dieses Plugin nur die Funktion zum Ausliefern der Thumbnail-Urls bereitstellt.  
Dieses Plugin erstellt keine Thumbnails! Dazu werden die entsprechenden Dienste verwendet.

Es gibt vier Paramter, die Ihnen optional zur Erstellung des Links zur Verfügung stehen:  
{mediaUrl}: Primär die Config shopware.cdn.url, alternativ Shop-Url  
{mediaPath}: Der relative Pfad zu dem Originalbild  
{width}: Die Breite des Thumbnails  
{height}: Die Höhe des Thumbnails  

Wir haben schon Erfahrung mit folgenden Diensten gemacht.
- [imgix](https://imgix.com) (kostenpflichtig, inkl. Webp)  
  Template-Beispiel: `https://<your-subdomain>.imgix.net/{mediaPath}?w={width}&h={height}`
- [BunnyCDN](https://bunnycdn.com) (kostenpflichtig, inkl. Webp)  
  Template-Beispiel: `{mediaUrl}/{mediaPath}?width={width}&height={height}`
- [keycdn](https://www.keycdn.com/support/image-processing) (kostenpflichtig, inkl. Webp)  
  Template-Beispiel: `{mediaUrl}/{mediaPath}?width={width}&height={height}`
- [imgproxy](https://imgproxy.net/) (kostenlos, selbst gehosted, inkl. Webp)  
  Template-Beispiel: `http://localhost:8080/insecure/fit/{width}/{height}/sm/0/plain/{mediaUrl}/{mediaPath}`
- [images.weserv.nl](https://images.weserv.nl/) (kostenlos, kein Webp)  
  Template-Beispiel: `https://images.weserv.nl/?url={mediaUrl}/{mediaPath}&w={width}&h={height}`
- [cloudimage](https://www.cloudimage.io/en/home) (kostenloser Plan verfügbar inkl. Webp)  
  Template-Beispiel: `https://token.cloudimg.io/v7/{mediaUrl}/{mediaPath}&w={width}&h={height}`

Jegliche Dienstleister, die per Url-Parameter Bildergrößen ändern, sollten aber auch kompatibel sein.

Nach der Einrichtung und Prüfung im DeveloperTools Ihres Browsers, können Sie auch den vorhandenen thumbnail-Ordner sichern und löschen.  

Dieses Plugin wird von [@FriendsOfShopware](https://store.shopware.com/friends-of-shopware.html) entwickelt.  
Maintainer dieses Plugins ist: [Sebastian König (tinect)](https://github.com/tinect)

Bei Fragen / Fehlern bitte ein [Github Issue](https://github.com/FriendsOfShopware/FroshPlatformThumbnailProcessor/issues/new) erstellen
