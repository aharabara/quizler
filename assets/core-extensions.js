/** @return {String} */
String.prototype.supplant = function (o) {
    return this.replace(/{([^{}]*)}/g,
        function (a, b) {
            var r = b.split('.').reduce((prev, curr) => prev ? prev[curr] : null, o);
            return typeof r === 'string' || typeof r === 'number' ? r : a;
        }
    );
};


