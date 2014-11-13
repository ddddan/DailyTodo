/* Auxiliary functions for dailytodo */
function isNumeric (obj) {
    return !isNaN(parseFloat(obj)) && isFinite(obj);
};


Array.prototype.deepSortAlpha = function () {
    var itm, L = arguments.length, order = arguments;

    var alphaSort = function (a, b) {
        if (!a && !b) {
            return 0;
        } else if (!a) {
            return 1;
        } else if (!b) {
            return -1;
        }
        if (!isNumeric(a)) {
            a = a.toLowerCase();
            b = b.toLowerCase();
        }
        if (a == b)
            return 0;
        return a > b ? 1 : -1;
    }
    if (!L)
        return this.sort(alphaSort);

    this.sort(function (a, b) {
        var tem = 0, indx = 0;
        while (tem == 0 && indx < L) {
            itm = order[indx];
            tem = alphaSort(a[itm], b[itm]);
            indx += 1;
        }
        return tem;
    });
    return this;
}

Element.prototype.hasClassName = function (name) {
    return new RegExp("(?:^|\\s+)" + name + "(?:\\s+|$)").test(this.className);
};

Element.prototype.addClassName = function (name) {
    if (!this.hasClassName(name)) {
        this.className = this.className ? [this.className, name].join(' ') : name;
    }
};

Element.prototype.removeClassName = function (name) {
    if (this.hasClassName(name)) {
        var c = this.className;
        this.className = c.replace(new RegExp("(?:^|\\s+)" + name + "(?!\\S)", "g"), "");
    }
};

// Remove options from a select dropdown
Element.prototype.removeOptions = function () {
    for (var i = this.options.length - 1; i >= 0; i--) {
        this.remove(i);
    }
};


