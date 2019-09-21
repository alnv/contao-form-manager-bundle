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
            // @todo
        },
        setInput: function () {
            for ( var strFieldname in this.model ) {
                if ( this.model.hasOwnProperty( strFieldname ) ) {
                    this.$parent.shared[ strFieldname ] = this.model[ strFieldname ];
                }
            }
            this.$parent.onChange( this );
        }
    },
    watch: {
        id: function (newId, oldId) {
            objInstances[ oldId ] = {
                initialized: this.initialized,
                palettes: this.palettes,
                model: this.model,
                type: this.type
            };
            if ( objInstances.hasOwnProperty( newId)  ) {
                objectAssign( this.$data, objInstances[ newId ] );
            }
            this.fetchBySource();
        }
    },
    mounted: function () {
        this.disableSubmit = this.disableSubmit === 'true';
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
                                '<component :is="field.component" :eval="field" :name="field.name" v-model="model[ field.name ]" v-on:input="setInput"></component>' +
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