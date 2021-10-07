let mix = require("laravel-mix");

mix.js("resources/js/nova.js", "dist/js")
    .vue()
    .postCss("resources/css/nova.css", "dist/css");
