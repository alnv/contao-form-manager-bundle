Vue.component( 'text-field', {
    data: function () {
        return {}
    },
    watch: {
        value: function() {
            // this.$emit( 'input', this.value );
        }
    },
    methods: {
        onInput: function () {
            var timeout = null;
            clearTimeout(timeout);
            timeout = setTimeout(function () {
                this.$emit( 'input', this.value );
            }.bind(this), 800);
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
            default: null,
            type: String|Array
        }
    },
    template:
    '<div class="field-component text">' +
        '<div class="field-component-container">' +
            '<label :for="\'id_\' + name">{{ eval.label }}</label>' +
            '<input type="text" v-model="value" :id="\'id_\' + name" @input="onInput()">' +
            '<template v-if="!eval.validate"><p class="error" v-for="message in eval.messages">{{ message }}</p></template>' +
            '<template v-if="eval.description"><p class="description">{{ eval.description }}</p></template>' +
        '</div>' +
    '</div>'
});
