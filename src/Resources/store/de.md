Mit diesem Plugin können Sie auf dynamische Thumbnails zurückgreifen.  
Jedes Aritkel, jedes Bild in der Einkaufswelten hat berechtigterweise Thumbnails. Diese werden standardmässig
automatisch beim Upload erzeugt und gespeichert.  
An diesem Punkt greift dieses Plugin ein und stellt die Funktion zur Verfügung, dass keine Thumbnails mehr erstellt
werden müssen.  
Die Thumbnails werden dann in Echtzeit beim Besuch durch einen externen Dienst erzeugt und ausgeliefert.

## Vorteile vom Sparen der Thumbnailerzeugung:
- Sparen von Speicherplatz
- Schnellerer Upload von Bildern
- Entlastung des Servers
- Schnellere Backups durch weniger Dateien

## Welchen Dienst verwende ich nun für die Thumbnails:
Wir haben schon Erfahrung mit folgenden Diensten gemacht.
- [BunnyCDN](https://bunnycdn.com) (kostenpflichtig)  
  Template-Beispiel: {mediaUrl}/{mediaPath}?width={width}&height={height}
- [keycdn](https://www.keycdn.com/support/image-processing) (kostenpflichtig)  
  Template-Beispiel: {mediaUrl}/{mediaPath}?width={width}&height={height}
- [imgproxy](https://imgproxy.net/) (kostenlos, selbst gehosted)  
  Template-Beispiel: http://localhost:8080/insecure/fit/{width}/{height}/sm/0/plain/{mediaUrl}/{mediaPath}
- [images.weserv.nl](https://images.weserv.nl/) (kostenlos)  
  Template-Beispiel: https://images.weserv.nl/?url={mediaUrl}/{mediaPath}&w={width}&h={height}

Jegliche Dienstleister, die per Url-Parameter Bildergrö0en ändern, sollten aber auch kompatibel sein.

Dieses Plugin wird von [@FriendsOfShopware](https://store.shopware.com/friends-of-shopware.html) entwickelt.  
Maintainer dieses Plugins ist: [Sebastian König (tinect)](https://github.com/tinect)

Bei Fragen / Fehlern bitte ein [Github Issue](https://github.com/FriendsOfShopware/FroshPlatformThumbnailProcessor/issues/new) erstellen
