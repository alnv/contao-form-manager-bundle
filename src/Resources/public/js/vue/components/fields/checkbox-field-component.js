Vue.component( 'checkbox-field', {
    data: function () {
        return {
            //
        }
    },
    methods: {
        //
    },
    watch: {
        value: function() {
            this.$emit( 'input', this.value );
            if ( this.eval.submitOnChange ) {
                this.$parent.submitOnChange( this.value, this.name )
            }
        }
    },
    props: {
        eval: {
            default: {},
            type: Object,
            required: true
        },
        name: {
            type: String,
            required: true
        },
        value: {
            default: null,
            type: String|Array
        }
    },
    template:
    '<div class="field-component checkbox">' +
        '<div class="field-component-container">' +
            '{{eval.label}}' +
            '<input type="checkbox" v-model="value" true-value="1" false-value="">' +
        '</div>' +
    '</div>'
});
