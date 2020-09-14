Vue.component( 'email-field', {
    data: function () {
        return {}
    },
    methods: {
        setCssClass: function() {
            let objCssClass = {};
            if (this.eval['tl_class']) {
                objCssClass[this.eval['tl_class']] = true;
            }
            if (this.eval['mandatory']) {
                objCssClass['mandatory'] = true;
            }
            if (this.eval['validate'] === false) {
                objCssClass['error'] = true;
            }
            objCssClass[this.name] = true;
            return objCssClass;
        }
    },
    watch: {
        value: function() {
            this.$emit('input', this.value);
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
        },
        idPrefix: {
            default: '',
            type: String ,
            required: false
        }
    },
    template:
        '<div class="field-component text" v-bind:class="setCssClass()">' +
            '<div class="field-component-container">' +
                '<label v-if="eval.label" class="label" :for="idPrefix + \'id_\' + name" v-html="eval.label"></label>' +
                '<input type="email" v-model="value" :id="idPrefix + \'id_\' + name" :placeholder="eval.placeholder" class="tl_text">' +
                '<template v-if="!eval.validate"><p class="error" v-for="message in eval.messages">{{ message }}</p></template>' +
                '<div v-if="eval.description" v-html="eval.description" class="info"></div>' +
            '</div>' +
        '</div>'
});
