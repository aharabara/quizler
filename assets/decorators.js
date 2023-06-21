/** add as a decorator over a method like "@log("something happened")"*/
function log(message){
    return function (methodRef, { name, addInitializer }) {
        addInitializer(function () {
            this[name] = function(...args) {
                console.log(message);
                return (methodRef.bind(this))(...args);
            };
        });
    }
}

export {
    log
}
