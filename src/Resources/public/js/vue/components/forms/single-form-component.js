const singleFormComponent = Vue.component( 'single-form', {
    data: function () {
        return {
            components: {},
            model: {}
        }
    },
    methods: {
        fetch: function () {
            this.$http.get( '/form-manager/getForm/' + this.table, {
                params: {
                    fields: this.fields
                }
            }).then( function ( objResponse ) {
                if ( objResponse.body ) {
                    console.log(objResponse.body);
                    /*
                    this['components'] = objResponse.body;
                    for ( var strName in this['components'] ) {
                        if ( this['components'].hasOwnProperty( strName ) ) {
                            this['model'][ strName ] = objResponse.body[ strName ]['value'];
                        }
                    }
                    */
                }
            });
        },
        onSubmit: function () {
            console.log( this.model );
        }
    },
    mounted: function () {
        this.fetch();
    },
    props: {
        table: {
            type: String,
            default: null,
            required: true
        },
        fields: {
            type: Array,
            default: []
        }
    },
    // @todo impl palettes handler
    template:
    '<div class="form-component">' +
        '<div class="form-component-container">' +
            '<form v-on:submit.prevent="onSubmit">' +
                '<template v-for="component in components" v-if="component.component">' +
                    '<component :is="component.component" :eval="component" name="component.name" v-model="model[ component.name ]"></component>' +
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