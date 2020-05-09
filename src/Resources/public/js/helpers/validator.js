const FormHelperValidator = {
    validateMinLength: function (value, minlength) {
        if (minlength === null) {
            return true;
        }
        return value.length >= minlength;
    },
    validateMaxLength: function (value, maxlength) {
        if (maxlength === null) {
            return true;
        }
        return value.length <= maxlength;
    },
    validateMandatory: function(value) {
        if (typeof value === 'undefined' || value === null) {
            return false;
        }
        if (typeof value === 'object') {
            if (value == null) {
                return false;
            }
            if (Array.isArray(value) && !value.length) {
                return false;
            }
        }
        return true;
    },
};