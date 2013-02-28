{
    "captcha": {
        "fontFile":     "{core}resources/fonts/KabobExtrabold.ttf"
    },

    "resources": {
        "directoryList": [
            {
                "name":     "scabbia/",
                "path":     "{core}resources/"
            }
        ],
        "directoryList:development": [
            {
                "name":     "docs/",
                "path":     "{core}resources/docs/",

                "autoViewer": {
                    "defaultPage":  "index.md",
                    "viewEngine":   "viewEngineMarkdown",
                    "header":       "{core}views/docs/header.php",
                    "footer":       "{core}views/docs/footer.php"
                }
            }
        ],

        "fileList": [],

        "packList": [
            {
                "name":         "scabbia.js",
                "type":         "js",
                "cacheTtl":     "86400",
                "partList": [
                    {
                        "type":         "file",
                        "class":        "core",
                        "path":         "{core}resources/laroux/laroux.js"
                    },
                    {
                        "type":         "function",
                        "class":        "core",
                        "name":         "Scabbia\\Extensions\\Mvc\\mvc::exportAjaxJs"
                    },
                    {
                        "type":         "file",
                        "class":        "jquery",
                        "path":         "{core}resources/jquery/sizzle.js"
                    },
                    {
                        "type":         "file",
                        "class":        "jquery",
                        "path":         "{core}resources/jquery/jquery-1.8.1-custom.js"
                    },
                    {
                        "type":         "file",
                        "class":        "jquery",
                        "path":         "{core}resources/jquery/jquery.fix.js"
                    },
                    {
                        "type":         "file",
                        "class":        "jqueryui",
                        "path":         "{core}resources/jquery/jquery.ui.core.js"
                    },
                    {
                        "type":         "file",
                        "class":        "jqueryui",
                        "path":         "{core}resources/jquery/jquery.ui.widget.js"
                    },
                    {
                        "type":         "file",
                        "class":        "jqueryui",
                        "path":         "{core}resources/jquery/jquery.ui.mouse.js"
                    },
                    {
                        "type":         "file",
                        "class":        "jqueryui",
                        "path":         "{core}resources/jquery/jquery.ui.position.js"
                    },
                    {
                        "type":         "file",
                        "class":        "jqueryui",
                        "path":         "{core}resources/jquery/jquery.ui.sortable.js"
                    },
                    {
                        "type":         "file",
                        "class":        "jqueryui",
                        "path":         "{core}resources/jquery/jquery.ui.tabs.js"
                    },
                    {
                        "type":         "file",
                        "class":        "jqueryui",
                        "path":         "{core}resources/jquery/jquery.ui.autocomplete.js"
                    },
                    {
                        "type":         "file",
                        "class":        "jqueryui",
                        "path":         "{core}resources/jquery/jquery.ui.datepicker.js"
                    },
                    {
                        "type":         "file",
                        "class":        "jqueryui",
                        "path":         "{core}resources/laroux/laroux.ui.js"
                    },
                    {
                        "type":         "file",
                        "class":        "jqueryuifx",
                        "path":         "{core}resources/jquery/jquery.effects.core.js"
                    },
                    {
                        "type":         "file",
                        "class":        "jqueryuifx",
                        "path":         "{core}resources/jquery/jquery.effects.highlight.js"
                    },
                    {
                        "type":         "file",
                        "class":        "jqueryuifx",
                        "path":         "{core}resources/jquery/jquery.effects.shake.js"
                    },
                    {
                        "type":         "file",
                        "class":        "validation",
                        "path":         "{core}resources/jquery/jquery.validate.js"
                    },
                    {
                        "type":         "file",
                        "class":        "validation",
                        "path":         "{core}resources/jquery/jquery.validate.additional-methods.js"
                    },
                    {
                        "type":         "file",
                        "class":        "map",
                        "path":         "{core}resources/mapbox/mapbox-0.6.5.js"
                    },
                    {
                        "type":         "file",
                        "class":        "cleditor",
                        "path":         "{core}resources/cleditor/jquery.cleditor.js"
                    },
                    {
                        "type":         "file",
                        "class":        "cleditor",
                        "path":         "{core}resources/cleditor/jquery.cleditor.xhtml.js"
                    },
                    {
                        "type":         "file",
                        "class":        "shadowbox",
                        "path":         "{core}resources/shadowbox/shadowbox.js"
                    },
                    {
                        "type":         "file",
                        "class":        "tablesorter",
                        "path":         "{core}resources/tablesorter/jquery.tablesorter.js"
                    },
                    {
                        "type":         "file",
                        "class":        "tablesorter",
                        "path":         "{core}resources/tablesorter/jquery.tablesorter.pager.js"
                    },
                    {
                        "type":         "file",
                        "class":        "snippet",
                        "path":         "{core}resources/snippet/jquery.snippet.js"
                    },
                    {
                        "type":         "file",
                        "class":        "flot",
                        "path":         "{core}resources/flot/jquery.flot.js"
                    },
                    {
                        "type":         "file",
                        "class":        "flot",
                        "path":         "{core}resources/flot/jquery.flot.pie.js"
                    },
                    {
                        "type":         "file",
                        "class":        "tipsy",
                        "path":         "{core}resources/tipsy/jquery.tipsy.js"
                    },
                    {
                        "type":         "file",
                        "class":        "maskedinput",
                        "path":         "{core}resources/maskedinput/jquery.maskedinput-1.3.js"
                    },
                    {
                        "type":         "file",
                        "class":        "blackmore",
                        "path":         "{core}resources/blackmore/blackmore.js"
                    }
                ]
            },
            {
                "name":         "scabbia.css",
                "type":         "css",
                "cacheTtl":     "86400",
                "partList": [
                    {
                        "type":         "file",
                        "class":        "reset",
                        "path":         "{core}resources/reset/reset.css"
                    },
                    {
                        "type":         "file",
                        "class":        "errorpages",
                        "path":         "{core}resources/errorpages/errorpages.css"
                    },
                    {
                        "type":         "file",
                        "class":        "jqueryui",
                        "path":         "{core}resources/jquery/jquery.ui.core.css"
                    },
                    {
                        "type":         "file",
                        "class":        "jqueryui",
                        "path":         "{core}resources/jquery/jquery.ui.autocomplete.css"
                    },
                    {
                        "type":         "file",
                        "class":        "jqueryui",
                        "path":         "{core}resources/jquery/jquery.ui.tabs.css"
                    },
                    {
                        "type":         "file",
                        "class":        "jqueryui",
                        "path":         "{core}resources/jquery/jquery.ui.datepicker.css"
                    },
                    {
                        "type":         "file",
                        "class":        "jqueryui",
                        "path":         "{core}resources/jquery/jquery.ui.theme.css"
                    },
                    {
                        "type":         "file",
                        "class":        "jqueryui",
                        "path":         "{core}resources/laroux/laroux.ui.css"
                    },
                    {
                        "type":         "file",
                        "class":        "map",
                        "path":         "{core}resources/mapbox/mapbox.0.6.5.css"
                    },
                    {
                        "type":         "file",
                        "class":        "cleditor",
                        "path":         "{core}resources/cleditor/jquery.cleditor.css"
                    },
                    {
                        "type":         "file",
                        "class":        "shadowbox",
                        "path":         "{core}resources/shadowbox/shadowbox.css"
                    },
                    {
                        "type":         "file",
                        "class":        "tablesorter",
                        "path":         "{core}resources/tablesorter/style.css"
                    },
                    {
                        "type":         "file",
                        "class":        "snippet",
                        "path":         "{core}resources/snippet/jquery.snippet.css"
                    },
                    {
                        "type":         "file",
                        "class":        "tipsy",
                        "path":         "{core}resources/tipsy/tipsy.css"
                    },
                    {
                        "type":         "file",
                        "class":        "blackmore",
                        "path":         "{core}resources/blackmore/blackmore.css"
                    },
                    {
                        "type":         "file",
                        "class":        "docs",
                        "path":         "{core}resources/docs/markdown.css"
                    }
                ]
            }
        ]
    }
}