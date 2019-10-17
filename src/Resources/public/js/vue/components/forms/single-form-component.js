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
const singleFormComponent = Vue.component( 'single-form', {
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
            this.$http.get( '/form-manager/' + strSource + '/' + this.identifier, {
                params: {
                    type: this.type,
                    initialized: this.initialized,
                    subpalettes: this.subpalettes
                }
            }).then(function ( objResponse ) {
                if ( objResponse.body ) {
                    var objModel = {};
                    for ( var i = 0; i < objResponse.body.length; i++ ) {
                        for ( var intKey in objResponse.body[i].fields ) {
                            if ( objResponse.body[i].fields.hasOwnProperty( intKey ) ) {
                                var strFieldname = objResponse.body[i].fields[ intKey ]['name'];
                                if ( !strFieldname ) {
                                    continue;
                                }
                                if ( strFieldname === 'type' ) {
                                    this.type = objResponse.body[i].fields[ intKey ]['value'];
                                }
                                objModel[ strFieldname ] = this.model[ strFieldname ] || objResponse.body[i].fields[ intKey ]['value'];
                            }
                        }
                    }
                    this.model = objModel;
                    this.initialized = true;
                    this.palettes = objResponse.body;
                }
            });
        },
        fetchBySource: function () {
            switch ( this.source ) {
                case 'dc':
                    this.fetch('getDcForm');
                    break;
                case 'form':
                    this.fetch('getForm');
                    break;
            }
        },
        submitOnChange: function ( strValue, strName, blnIsSelector ) {
            if ( strName === 'type' ) {
                this.type = strValue;
            }
            if ( blnIsSelector === true ) {
                this.subpalettes[ strName ] = strName + '::' + strValue;
            }
            this.fetchBySource();
        },
        onSubmit: function () {
            var objParent = this.getParentSharedInstance(this.$parent);
            this.getSubmitPromise().then(function ( objResponse ) {
                if ( objResponse.body ) {
                    this.setValidation( objResponse.body );
                    if ( objResponse.body.success ) {
                        this.setActiveStateInMultipleForm();
                        objParent.onChange( this );
                    }
                    for ( var x = 0; x < this.$children.length; x++ ) {
                        this.$children[x].$forceUpdate();
                    }
                }
            });
        },
        getSubmitPromise: function() {
            return this.$http.post( '/form-manager/validate/form/' + this.identifier, this.model,{
                emulateJSON: true,
                'Content-Type': 'application/x-www-form-urlencoded'
            });
        },
        setValidation: function( objResponse ) {
            for ( var j = 0; j < this.palettes.length; j++ ) {
                for ( var f = 0; f < this.palettes[j].fields.length; f++ ) {
                    for ( var i = 0; i < objResponse.errors.length; i++ ) {
                        if ( objResponse.errors[i].name ===  this.palettes[j].fields[f].name ) {
                            this.palettes[j].fields[f]['messages'] = objResponse.errors[i].message;
                            this.palettes[j].fields[f]['validate'] = objResponse.errors[i].validate;
                        }
                    }
                }
            }
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
            if ( this.$parent.forms ) {
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
                type: this.type
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
        disableSubmit: {
            type: Boolean,
            default: false
        }
    },
    template:
    '<div class="form-component">' +
        '<div class="form-component-container">' +
            '<form v-on:submit.prevent="onSubmit">' +
                '<template v-for="palette in palettes">' +
                    '<div class="palette" v-bind:class="palette.name">' +
                        '<div class="palette-container">' +
                            '<template v-for="field in palette.fields" v-if="field.component">' +
                                '<component :is="field.component" :eval="field" :name="field.name" v-model="model[field.name]" v-on:input="setInput(field)"></component>' +
                            '</template>' +
                        '</div>' +
                    '</div>' +
                '</template>' +
                '<div v-if="!disableSubmit" class="form-buttons-container">' +
                    '<div class="form-submit">' +
                        '<button type="submit" class="submit">Senden</button>' +
                    '</div>' +
                '</div>' +
            '</form>' +
        '</div>' +
    '</div>'
});