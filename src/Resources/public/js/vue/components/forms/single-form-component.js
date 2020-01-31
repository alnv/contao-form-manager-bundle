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
const singleFormComponent = Vue.component( 'single-form', function (resolve, reject) {
    resolve({
        data: function () {
            return {
                initialized: false,
                subpalettes: {},
                palettes: [],
                model: {},
                type: ''
            }
        },
        methods: {
            fetch: function (strSource) {
                this.$http.get( '/form-manager/get' + strSource + '/' + this.identifier, {
                    params: {
                        type: this.type,
                        attributes: this.attributes,
                        initialized: this.initialized,
                        subpalettes: this.subpalettes
                    }
                }).then(function ( objResponse ) {
                    if ( objResponse.body && objResponse.ok ) {
                        this.initialized = true;
                        this.model = this.setModel( objResponse.body );
                        this.setPalette( objResponse.body );
                        this.$parent.clearAlert();
                    }
                    if ( !objResponse.ok ) {
                        this.$parent.setErrorAlert('',this);
                    }
                });
            },
            setModel: function( palettes ) {
                var objModel = {};
                for ( var i = 0; i < palettes.length; i++ ) {
                    for ( var intKey in palettes[i].fields ) {
                        if ( palettes[i].fields.hasOwnProperty( intKey ) ) {
                            var strFieldname = palettes[i].fields[ intKey ]['name'];
                            if ( !strFieldname ) {
                                continue;
                            }
                            if ( strFieldname === 'type' ) {
                                this.type = palettes[i].fields[ intKey ]['value'];
                            }
                            objModel[ strFieldname ] = this.model[ strFieldname ] || palettes[i].fields[ intKey ]['value'];
                        }
                    }
                }
                return objModel;
            },
            getSource: function() {
                switch ( this.source ) {
                    case 'dc':
                        return 'DcForm';
                    case 'form':
                        return 'Form';
                }
            },
            fetchBySource: function () {
                this.fetch( this.getSource() )
            },
            submitOnChange: function ( strValue, strName, blnIsSelector ) {
                if ( strName === 'type' ) {
                    this.type = strValue;
                }
                if ( blnIsSelector === true ) {
                    this.subpalettes[ strName ] = strName + '::' + strValue;
                }
                this.$parent.setLoadingAlert('', this);
                this.fetchBySource();
                for ( var j = 0; j < this.$children.length; j++ ) {
                    if ( this.$children[j].$vnode.tag && typeof this.$children[j].onChange !== 'undefined' ) {
                        var objShare = {};
                        objShare[ strName ] = strValue;
                        this.$children[j].onChange( objShare );
                    }
                }
            },
            onSubmit: function () {
                var objParent = this.getParentSharedInstance(this.$parent);
                this.$parent.setLoadingAlert('', this);
                this.getSubmitPromise().then( function ( objResponse ) {
                    if ( objResponse.body ) {
                        this.setPalette( objResponse.body.form );
                        if ( objResponse.body.success ) {
                            if ( this.$parent.forms ) {
                                this.setActiveStateInMultipleForm();
                            } else {
                                var strRedirect = this.successRedirect;
                                if ( typeof objResponse.body['redirect'] !== 'undefined' && objResponse.body['redirect'] ) {
                                    strRedirect = objResponse.body['redirect'];
                                }
                                if ( strRedirect ) {
                                    window.location.href = strRedirect;
                                }
                            }
                            objParent.onChange( this );
                            this.$parent.clearAlert();
                        } else {
                            this.$parent.setErrorAlert('', this);
                        }
                    }
                });
            },
            getSubmitPromise: function() {
                return this.$http.post( '/form-manager/'+ ( this.validateOnly ? 'validate' : 'save' ) +'/' + this.source + '/' + this.identifier + this.getParameters(), this.model, {
                    emulateJSON: true,
                    'Content-Type': 'application/x-www-form-urlencoded'
                });
            },
            getParameters: function() {
                var arrParameters = [];
                if ( typeof this.attributes !== 'undefined' && this.attributes ) {
                    for (var strName in this.attributes) {
                        if (this.attributes.hasOwnProperty(strName)) {
                            arrParameters.push('attributes[' + strName + ']' + '=' + encodeURIComponent(this.attributes[strName]));
                        }
                    }
                }
                if ( typeof this.subpalettes !== 'undefined' && this.subpalettes ) {
                    for (var strSubPalette in this.subpalettes) {
                        if (this.subpalettes.hasOwnProperty(strSubPalette)) {
                            arrParameters.push('subpalettes[]' + '=' + encodeURIComponent(this.subpalettes[strSubPalette]));
                        }
                    }
                }
                arrParameters.push( 'type=' + encodeURIComponent( this.type ) );
                arrParameters.push( 'initialized=' + encodeURIComponent( this.initialized ) );
                return '?' + arrParameters.join('&');
            },
            setPalette: function ( palette ) {
                this.palettes = palette;
            },
            setInput: function (field) {
                var objParent = this.getParentSharedInstance(this.$parent);
                for ( var strFieldname in this.model ) {
                    if ( this.model.hasOwnProperty( strFieldname ) ) {
                        objParent.shared[ strFieldname ] = this.model[ strFieldname ];
                    }
                }
                if ( field['isReactive'] ) {
                    objParent.onChange( this );
                }
            },
            setActiveStateInMultipleForm: function () {
                for ( var i = 0; i < this.$parent.forms.length; i++ ) {
                    if ( this.identifier === this.$parent.forms[i]['identifier'] && this.source === this.$parent.forms[i]['source'] ) {
                        this.$parent.forms[i]['valid'] = true;
                        if ( !this.$parent.forms[i]['valid'] ) {
                            continue;
                        }
                        if ( this.$parent.forms[i+1] ) { // has next?
                            this.$parent.setActive( this.$parent.forms[i+1], i+1 );
                        }
                        else {
                            this.$parent.setComplete();
                        }
                    }
                }
            },
            getParentSharedInstance: function(parent) {
                if ( typeof parent.shared === 'undefined' ) {
                    return this.getParentSharedInstance(parent.$parent);
                }
                return parent;
            },
            saveInstance: function () {
                objInstances[ this.id ] = {
                    initialized: this.initialized,
                    palettes: this.palettes,
                    model: this.model,
                    type: this.type,
                    _source: this.source,
                    _formId: this.identifier
                };
            },
            getInstance: function (id) {
                if ( objInstances.hasOwnProperty(id)  ) {
                    objectAssign( this.$data, objInstances[id] );
                }
            }
        },
        watch: {
            id: function (newId) {
                this.getInstance(newId);
                this.fetchBySource();
            },
            mode: {
                handler: function () {
                    for ( var i = 0; i < this.palettes.length; i++ ) {
                        for ( var intKey in this.palettes[i].fields ) {
                            if ( this.palettes[i].fields.hasOwnProperty( intKey ) ) {
                                //
                            }
                        }
                    }
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
            attributes: {
                default: {},
                type: Object,
                required: false
            }
        },
        template:
            '<div class="form-component">' +
                '<div class="form-component-container">' +
                    '<form v-on:submit.prevent="onSubmit">' +
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
                                '<button type="submit" class="submit">{{ submitLabel }}</button>' +
                            '</div>' +
                        '</div>' +
                    '</form>' +
                '</div>' +
            '</div>'
    });
});