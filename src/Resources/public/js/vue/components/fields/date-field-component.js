Vue.component( 'date-field', {
    data: function () {
        return {
            //
        }
    },
    methods: {
        setCssClass: function() {
            let objCssClass = {};
            if ( this.eval['tl_class'] ) {
                objCssClass[this.eval['tl_class']] = true;
            }
            if ( this.eval['mandatory'] ) {
                objCssClass['mandatory'] = true;
            }
            return objCssClass;
        }
    },
    watch: {
        value: function() {
            this.$emit( 'input', this.value );
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
            type: Object,
            default: null,
            required: false
        },
        idPrefix: {
            default: '',
            type: String ,
            required: false
        }
    },
    template:
    '<div class="field-component date" v-bind:class="setCssClass()">' +
        '<div class="field-component-container">' +
            '<label :for="idPrefix + \'id_\' + name">{{ eval.label }}</label>' +
            '<input type="text" v-model="value" :id="idPrefix + \'id_\' + name" v-pikaday>' +
            '<template v-if="!eval.validate"><p class="error" v-for="message in eval.messages">{{ message }}</p></template>' +
            '<template v-if="eval.description"><p class="description">{{ eval.description }}</p></template>' +
        '</div>' +
    '</div>'
});

