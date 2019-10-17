Vue.component( 'radio-field', {
    data: function () {
        return {
            //
        }
    },
    methods: {
        checked: function ( strValue ) {
            if ( Array.isArray(this.value) ) {
                return this.value.indexOf( strValue ) !== -1;
            }
            return this.value === strValue;
        }
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
            default: '',
            type: Object,
            required: true
        },
        value: {
            type: String,
            default: null
        }
    },
    template:
    '<div class="field-component radio">' +
        '<div class="field-component-container">' +
            '<p class="label">{{eval.label}}</p>' +
            '<span v-for="(option, index) in eval.options" class="radio-container" v-bind:class="{ \'checked\': checked( option.value ) }">' +
                '<input type="radio" v-model="value" :value="option.value" :id="\'id_\' + name + \'_\' + index">' +
                '<label :for="\'id_\' + name + \'_\' + index">{{option.label}}</label>' +
            '</span>' +
            '<template v-if="!eval.validate"><p class="error" v-for="message in eval.messages">{{ message }}</p></template>' +
            '<template v-if="eval.description"><p class="description">{{ eval.description }}</p></template>' +
        '</div>' +
    '</div>'
});
