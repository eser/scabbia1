{
    "info": {
        "name":             "Mvc",
        "version":          "1.1.0",
        "license":          "GPLv3",
        "phpversion":       "5.3.0",
        "phpdependList":    [],
        "fwversion":        "1.1",
        "fwdependList":     [
            "Http",
            "Io",
            "Models",
            "Router",
            "String",
            "Views"
        ]
    },
    "eventList": [
        {
            "name":         "load",
            "type":         "callback",
            "value":        "Scabbia\\Extensions\\Mvc\\Mvc::extensionLoad"
        },
        {
            "name":         "httpUrl",
            "type":         "callback",
            "value":        "Scabbia\\Extensions\\Mvc\\mvc::httpUrl"
        }

    ]
}
