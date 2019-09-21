Vue.component( 'select-field', {
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
                this.$parent.submitOnChange( this.value, this.name, this.eval['isSelector'] )
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
            default: '',
            type: String,
            required: true
        },
        value: {
            default: '',
            required: false,
            type: String|Array
        }
    },
    template:
    '<div class="field-component select">' +
        '<div class="field-component-container">' +
            '<label :for="name">{{ eval.label }}</label>' +
            '<select v-model="value" :id="name" :multiple="eval.multiple">' +
                '<option value="">-</option>' +
                '<option v-for="option in eval.options" v-bind:value="option.value">{{option.label}}</option>' +
            '</select>' +
        '</div>' +
    '</div>'
});

