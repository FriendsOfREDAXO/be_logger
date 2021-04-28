
# Addon BE_Logger - Backend-Logging

Mit dem Addon `be_logger` können die Backend-Zugriffe aller Benutzer in einer Logging-Tabelle ausgegeben werden.
Es gibt eine Anzeigefunktion der Logging-Tabelle mit Filterfunktion.
Das Addon ist ausschließlich für Administratoren gedacht!

Hier kann nach

- Datum
- Login
- Name
- Methode
- Page (Addon)
- Params (GET/Post)

gefiltert werden.

Es werden generell von allen Benutzern alle Zugriffe geloggt.

In den Addon-Einstellungen können Backend-Seiten bzw. Addons vom logging ausgeschlossen werden (kommaseparierte Liste).

Benutzer können auch durch Angabe einer kommaseparierten Liste vom logging ausgeschlossen werden.

Ältere Tabellen-Einträge können automatisch gelöscht werden (Vorgabe der verbleibenden Tage in den Einstellungen).
