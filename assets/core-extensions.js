String.prototype.textFromHTML = function () {
    return this
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

String.prototype.toInt = function () {
    return parseInt(this);
}


/** @return {String} */
String.prototype.supplant = function (o) {
    return this.replace(/{([^{}]*)}/g,
        function (a, b) {
            var r = b.split('.').reduce((prev, curr) => prev ? prev[curr] : null, o);
            return typeof r === 'string' || typeof r === 'number' ? r : a;
        }
    );
};

Array.prototype.range = function(start, end = null){
    if(end === null){
        end = start;
        start = 0;
    }
    return Array.from({length: end}, (x, i) => i + start);
}

import { Remarkable } from 'remarkable';
import hljs from 'highlight.js' // https://highlightjs.org/

// Actual default values
var md = new Remarkable({
    highlight: function (str, lang) {
        if (lang && hljs.getLanguage(lang)) {
            try {
                return hljs.highlight(lang, str).value;
            } catch (err) {}
        }

        try {
            return hljs.highlightAuto(str).value;
        } catch (err) {}

        return ''; // use external default escaping
    }
});

Object.defineProperty(String.prototype, "markdowned", {
    get: function markdowned() {
        return md.render(this)
    }
});


