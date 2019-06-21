const singleFormComponent = Vue.component( 'single-form', {
    data: function () {
        return {
            activeSubPalettes: {},
            palettes: [],
            model: {}
        }
    },
    methods: {
        fetch: function () {
            this.$http.get( '/form-manager/getForm/' + this.table, {
                params: {
                    type: this.type,
                    subPalettes: this.activeSubPalettes
                }
            }).then( function ( objResponse ) {
                if ( objResponse.body ) {
                    this.subPalettes = objResponse.body.subPalettes;
                    this.palettes = objResponse.body.palettes;
                    for ( var i = 0; i < objResponse.body.palettes.length; i++ ) {
                        for ( var strFieldname in objResponse.body.palettes[i].fields ) {
                            if ( objResponse.body.palettes[i].fields.hasOwnProperty( strFieldname ) ) {
                                this.model[ strFieldname ] = this.model[ strFieldname ] || objResponse.body.palettes[i].fields[ strFieldname ]['value'];
                            }
                        }
                    }
                }
            });
        },
        submitOnChange: function ( strValue, strName ) {
            if ( this.subPalettes.indexOf( strName ) !== -1 ) {
                if ( strName === 'type' ) {
                    this.type = strValue;
                    this.fetch();
                    return null;
                }
                if ( strValue ) {
                    this.activeSubPalettes[ strName ] = [ strName, strName + '_' + strValue ];
                    this.fetch();
                }
                else {
                    delete this.activeSubPalettes[ strName ];
                    this.fetch();
                }
            }
        },
        onSubmit: function () {
            console.log( this.model );
        }
    },
    mounted: function () {
        this.fetch();
    },
    props: {
        subPalettes: {
            type: Array,
            default: []
        },
        table: {
            type: String,
            default: null,
            required: true
        },
        type: {
            type: String,
            default: null
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
                        '<button>Senden</button>' +
                    '</div>' +
                '</div>' +
            '</form>' +
        '</div>' +
    '</div>'
});