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
            activeSubPalettes: {},
            initialized: false,
            subPalettes: [],
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
                    subPalettes: this.activeSubPalettes
                }
            }).then( function ( objResponse ) {
                if ( objResponse.body ) {
                    var objModel = {};
                    this.subPalettes = objResponse.body.subPalettes;
                    this.palettes = objResponse.body.palettes;
                    this.type = objResponse.body.type;
                    this.initialized = true;
                    for ( var i = 0; i < objResponse.body.palettes.length; i++ ) {
                        for ( var intKey in objResponse.body.palettes[i].fields ) {
                            if ( objResponse.body.palettes[i].fields.hasOwnProperty( intKey ) ) {
                                var strFieldname = objResponse.body.palettes[i].fields[ intKey ]['name'];
                                if ( !strFieldname ) {
                                    continue;
                                }
                                objModel[ strFieldname ] = this.model[ strFieldname ] || objResponse.body.palettes[i].fields[ intKey ]['value'];
                            }
                        }
                    }
                    this.model = objModel;
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
        submitOnChange: function ( strValue, strName ) {
            if ( this.setSubPalettes( strValue, strName ) ) {
                this.fetchBySource();
            }
        },
        setSubPalettes: function ( strValue, strName ) {
            if ( this.subPalettes.indexOf( strName ) !== -1 ) {
                if ( strName === 'type' ) {
                    this.type = strValue;
                    return true;
                }
                if ( strValue ) {
                    this.activeSubPalettes[ strName ] = [ strName, strName + '_' + strValue ];
                }
                else {
                    delete this.activeSubPalettes[ strName ];
                }
                return true;
            }
            return false;
        },
        onSubmit: function () {
            console.log( this.model );
        }
    },
    watch: {
        id: function (newId, oldId) {
            objInstances[ oldId ] = {
                activeSubPalettes: this.activeSubPalettes,
                initialized: this.initialized,
                subPalettes: this.subPalettes,
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
                                '<component :is="field.component" :eval="field" :name="field.name" v-model="model[ field.name ]"></component>' +
                            '</template>' +
                        '</div>' +
                    '</div>' +
                '</template>' +
                '<div class="form-buttons-container">' +
                    '<div class="form-submit">' +
                        '<button type="submit" class="submit">Senden</button>' +
                    '</div>' +
                '</div>' +
            '</form>' +
        '</div>' +
    '</div>'
});