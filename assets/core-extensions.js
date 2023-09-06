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
