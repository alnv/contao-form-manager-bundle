function toObject(value) {
    if ( value === null || value === undefined ) {
        throw new TypeError('Object.assign cannot be called with null or undefined');
    }
    return Object(value);
}
function objectAssign(target, source) {
    var from;
    var symbols;
    var to = toObject(target);
    var hasOwnProperty = Object.prototype.hasOwnProperty;
    var getOwnPropertySymbols = Object.getOwnPropertySymbols;
    var propIsEnumerable = Object.prototype.propertyIsEnumerable;
    for (var s = 1; s < arguments.length; s++) {
        from = Object(arguments[s]);
        for (var key in from) {
            if (hasOwnProperty.call(from, key)) {
                to[key] = from[key];
            }
        }
        if (getOwnPropertySymbols) {
            symbols = getOwnPropertySymbols(from);
            for (var i = 0; i < symbols.length; i++) {
                if (propIsEnumerable.call(from, symbols[i])) {
                    to[symbols[i]] = from[symbols[i]];
                }
            }
        }
    }
    return to;
}
var objInstances = {};
const singleFormComponent = Vue.component('single-form', function (resolve, reject) {
    resolve({
        data: function () {
            return {
                progress: false,
                initialized: false,
                cartSubmitted: false,
                subpalettes: {},
                once: false,
                palettes: [],
                model: {},
                type: ''
            }
        },
        methods: {
            fetch: function (strSource) {

                if (this.formData && !this.once) {
                    this.model = this.setModel(this.formData);
                    this.setPalette(this.formData);
                    this.initialized = true;
                    this.once = true;
                    if (this.palettes.length) {
                        return null;
                    }
                }

                let objParent = this.getParentSharedInstance(this.$parent);

                this.$http.get(this.absoluteUrl+'/form-manager/get' + strSource + '/' + this.identifier, {
                    params: {
                        id: this.id,
                        type: this.type,
                        hash: (window.location.hash ? window.location.hash.substr(1) : ''),
                        language: (this.language ? this.language : ''),
                        attributes: this.attributes,
                        initialized: this.initialized,
                        subpalettes: this.subpalettes
                    }
                }).then(function(objResponse) {
                    if (objResponse.body && objResponse.ok) {
                        this.model = this.setModel(objResponse.body);
                        this.setPalette(objResponse.body);
                        this.initialized = true;
                        objParent.clearAlert();
                    }
                    if (!objResponse.ok) {
                        objParent.setErrorAlert('',this);
                    }
                }.bind(this));
            },
            setModel: function(palettes) {
                let objModel = {};

                /*
                if (this.useStorage) {
                    let varStorage = localStorage.getItem('model-' + this.id);
                    if (varStorage) {
                        this.model = JSON.parse(varStorage);
                        this.triggerOnInput();
                    }
                }
                */

                if (this.model && this.model.hasOwnProperty('id')) {
                    objModel['id'] = this.model['id'];
                }

                for (let i=0; i<palettes.length; i++) {
                    for (let intKey in palettes[i].fields) {
                        if (palettes[i].fields.hasOwnProperty(intKey)) {
                            let strFieldname = palettes[i].fields[intKey]['name'];
                            if (!strFieldname) {
                                continue;
                            }
                            if (strFieldname === 'type') {
                                this.type = palettes[i].fields[intKey]['value'];
                            }
                            if (!this.model) {
                                objModel[strFieldname] = palettes[i].fields[intKey]['value'];
                            }else {
                                objModel[strFieldname] = this.model[strFieldname] || palettes[i].fields[intKey]['value'];
                            }
                        }
                    }
                }

                if (this.id && typeof objInstances[this.id] !== 'undefined') {
                    for (let strName in objModel) {
                        if (objModel.hasOwnProperty(strName)) {
                            objModel[strName] = objInstances[this.id]['model'][strName];
                        }
                    }
                }

                if (window.VueData._modal) {
                    if (objModel.hasOwnProperty(window.VueData._modal.field) && window.VueData._modal.created) {
                        objModel[window.VueData._modal.field] = window.VueData._modal.created;
                    }
                }

                if (this.model) {
                    for(let strName in this.model) {
                        if (!this.model.hasOwnProperty(strName)) {
                            continue;
                        }
                        if (!objModel.hasOwnProperty(strName)) {
                            objModel[strName] = this.model[strName];
                        }
                    }
                }

                return objModel || {};
            },
            getSource: function() {
                switch (this.source) {
                    case 'dc':
                        return 'DcForm';
                    case 'form':
                        return 'Form';
                }
            },
            fetchBySource: function () {
                this.fetch(this.getSource())
            },
            submitOnChange: function (strValue, strName, blnIsSelector) {

                if (strName === 'type') {
                    this.type = strValue;
                }

                if (blnIsSelector === true) {
                    this.subpalettes[strName] = strName + '::' + strValue;
                }

                this.attributes[strName] = strValue;
                let objParent = this.getParentSharedInstance(this.$parent);
                objParent.setLoadingAlert('', this);

                this.fetchBySource();
                let objShare = {};

                for (let j = 0; j < this.$children.length; j++) {
                    if ( this.$children[j].$vnode.tag && typeof this.$children[j].onChange !== 'undefined' ) {
                        objShare[strName] = strValue;
                        this.$children[j].onChange(objShare);
                    }
                }
            },
            onChange: function(share,component) {
                if (typeof component !== 'undefined' && typeof component.$vnode !== 'undefined' && component.$vnode !== null) {
                    if (component.$vnode.componentOptions.tag === 'modal-view') {
                        this.fetchBySource();
                    }
                }
            },
            onSubmit: function () {
                if (this.progress) {
                    return null;
                }
                this.progress = true;
                let objParent = this.getParentSharedInstance(this.$parent);
                objParent.setLoadingAlert('', this);
                this.getSubmitPromise().then(function (objResponse) {
                    if (objResponse.body) {
                        this.setPalette(objResponse.body.form);
                        if (objResponse.body.success) {
                            if (this.$parent.forms) {
                                this.setActiveStateInMultipleForm();
                            } else {
                                if (this.addCart) {
                                    this.add2Cart(this.cart.product, this.cart.units, this.cart.cid, this.cart.options);
                                    return null;
                                }
                                let strRedirect = this.successRedirect;
                                if (this.submitCallback && typeof this.submitCallback === 'function') {
                                    strRedirect = this.submitCallback(this, objResponse.body);
                                }
                                if (typeof objResponse.body['redirect'] !== 'undefined' && objResponse.body['redirect']) {
                                    strRedirect = objResponse.body['redirect'];
                                }
                                if (strRedirect) {
                                    this.progress = true;
                                    // localStorage.setItem('model-' + this.id, '');
                                    window.location.href = strRedirect;
                                    return null;
                                }
                            }
                            this.progress = false;
                            objParent.onChange(this);
                            objParent.clearAlert();
                        } else {
                            this.progress = false;
                            objParent.setErrorAlert(objResponse.body.message, this);
                        }
                    }
                }.bind(this));
            },
            getSubmitPromise: function() {
                return this.$http.post(this.absoluteUrl+'/form-manager/'+ (this.validateOnly ? 'validate' : 'save') +'/' + this.source + '/' + this.identifier + this.getParameters(), this.model, {
                    emulateJSON: true,
                    'Content-Type': 'application/x-www-form-urlencoded'
                });
            },
            add2Cart: function (productId, units, cid, options) {
                let attributes = {};
                this.cartSubmitted = true;
                attributes[this.id] = this.model;
                if (this.model['quantity'] || this.model['units']) {
                    units = this.model['quantity'];
                }
                this.$http.post(this.absoluteUrl+'/shop-manager/addCart', {
                    cid: cid,
                    units: units,
                    productId: productId,
                    attributes: attributes,
                    language: this.language
                }, {
                    emulateJSON: true
                }).then(function(objResponse) {
                    if (!objResponse.body.error) {
                        this.model = {};
                        // localStorage.setItem('model-' + this.id, '');
                        this.getParentSharedInstance(this.$parent).cartActive = true;
                        this.getParentSharedInstance(this.$parent).onChange(this);
                        if (objResponse.body.message) {
                            this.getParentSharedInstance(this.$parent).setLoadingAlert(objResponse.body.message, this);
                            this.getParentSharedInstance(this.$parent).clearAlert();
                        } else {
                            this.getParentSharedInstance(this.$parent).clearAlert();
                        }
                        this.cartSubmitted = false;
                    }else {
                        this.getParentSharedInstance(this.$parent).setErrorAlert(objResponse.body.message, this);
                    }
                }.bind(this));
            },
            getParameters: function() {
                let arrParameters = [];
                if (typeof this.attributes !== 'undefined' && this.attributes) {
                    for (let strName in this.attributes) {
                        // if (this.attributes.hasOwnProperty(strName)) {
                            arrParameters.push('attributes[' + strName + ']' + '=' + encodeURIComponent(this.attributes[strName]));
                        //}
                    }
                }
                if (typeof this.subpalettes !== 'undefined' && this.subpalettes) {
                    for (let strSubPalette in this.subpalettes) {
                        // if (this.subpalettes.hasOwnProperty(strSubPalette)) {
                            arrParameters.push('subpalettes[]' + '=' + encodeURIComponent(this.subpalettes[strSubPalette]));
                        // }
                    }
                }

                arrParameters.push('id=' + encodeURIComponent(this.id));
                arrParameters.push('type=' + encodeURIComponent(this.type));
                arrParameters.push('language=' + encodeURIComponent((this.language ? this.language : '')));
                arrParameters.push('initialized=' + encodeURIComponent(this.initialized));
                return '?' + arrParameters.join('&');
            },
            setPalette: function (palette) {
                this.palettes = palette;
            },
            setInput: function (field) {
                let objParent = this.getParentSharedInstance(this.$parent);
                for (let strFieldname in this.model) {
                    if (this.model.hasOwnProperty(strFieldname)) {
                        objParent.shared[strFieldname] = this.model[strFieldname];
                    }
                }
                this.triggerOnInput();
                if (field['isReactive']) {
                    objParent.onChange(this);
                }
            },
            triggerOnInput: function() {
                var objParent = this.getParentSharedInstance(this.$parent);
                if (objParent.onInput && typeof objParent.onInput === 'function') {
                    objParent.onInput(this.model, this);
                }
            },
            setActiveStateInMultipleForm: function () {
                for (var i = 0; i < this.$parent.forms.length; i++) {
                    if (this.identifier === this.$parent.forms[i]['identifier'] && this.source === this.$parent.forms[i]['source']) {
                        this.$parent.forms[i]['valid'] = true;
                        if (!this.$parent.forms[i]['valid']) {
                            continue;
                        }
                        if (this.$parent.forms[i+1]) {
                            this.$parent.setActive(this.$parent.forms[i+1], i+1);
                        }
                        else {
                            this.$parent.setComplete();
                        }
                    }
                }
            },
            getParentSharedInstance: function(parent) {
                if (typeof parent.$parent !== 'undefined') {
                    return this.getParentSharedInstance(parent.$parent);
                }
                return parent;
            },
            saveInstance: function () {
                objInstances[this.id] = {
                    initialized: this.initialized,
                    language: this.language,
                    palettes: this.palettes,
                    model: this.model,
                    type: this.type,
                    _source: this.source,
                    _formId: this.identifier
                };
            },
            getInstance: function (id) {
                if (objInstances.hasOwnProperty(id)) {
                    objectAssign(this.$data,objInstances[id]);
                }
            }
        },
        watch: {
            id: function (newId) {
                this.getInstance(newId);
                this.fetchBySource();
            },
            model: {
                handler: function () {
                    /*
                    try {
                        localStorage.setItem('model-' + this.id, JSON.stringify(this.model));
                    } catch(e) {
                        if(e.name === "NS_ERROR_FILE_CORRUPTED") {
                            //
                        }
                    }
                    */
                },
                deep: true
            }
        },
        mounted: function () {
            this.getInstance(this.id);
            this.fetchBySource();
        },
        props: {
            id: {
                type: String,
                default: null
            },
            identifier: {
                type: String,
                default: null,
                required: true
            },
            source: {
                type: String,
                default: null,
                required: true
            },
            validateOnly: {
                type: Boolean,
                default: false
            },
            disableSubmit: {
                type: Boolean,
                default: false
            },
            model: {
                default: {},
                type: Object,
                required: false
            },
            submitLabel: {
                type: String,
                default: 'Senden',
                required: false
            },
            successRedirect: {
                type: String,
                default: '',
                required: false
            },
            disableLoader: {
                type: Boolean,
                default: false,
                required: false
            },
            attributes: {
                default: {},
                type: Object,
                required: false
            },
            submitCallback: {
                default: null,
                type: Function,
                required: false
            },
            useStorage: {
                type: Boolean,
                default: false,
                required: false
            },
            language: {
                type: String,
                default: null,
                required: false
            },
            addCart: {
                type: Boolean,
                default: false,
                required: false
            },
            cart: {
                default: {},
                type: Object,
                required: false
            },
            formData: {
                type: Object,
                default: null,
                required: false
            },
            absoluteUrl: {
                default: '',
                type: String,
                required: false
            },
            pageId: {
                default: '',
                type: String,
                required: false
            }
        },
        template:
            '<div class="form-component">' +
                '<div class="form-component-container">' +
                    '<form v-if="palettes.length" v-on:submit.prevent="onSubmit">' +
                        '<template v-for="palette in palettes">' +
                            '<div class="palette" v-bind:class="palette.name">' +
                                '<p v-if="palette.label" class=palette-name>{{ palette.label }}</p>' +
                                '<div class="palette-container">' +
                                    '<template v-for="field in palette.fields" v-if="field.component">' +
                                        '<component :is="field.component" :eval="field" :name="field.name" v-model="model[field.name]" v-on:input="setInput(field)"></component>' +
                                    '</template>' +
                                '</div>' +
                            '</div>' +
                        '</template>' +
                        '<slot></slot>' +
                        '<div v-if="!disableSubmit" class="form-buttons-container">' +
                            '<div class="form-submit">' +
                                '<button type="submit" class="submit" v-html="submitLabel"></button>' +
                            '</div>' +
                        '</div>' +
                    '</form>' +
                    '<loading v-if="!palettes.length && !disableLoader"></loading>' +
                '</div>' +
            '</div>'
    });
});