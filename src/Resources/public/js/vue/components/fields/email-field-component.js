Vue.component('email-field', {
    data: function () {
        return {
            css: {}
        }
    },
    methods: {
        setCssClass: function() {
            if (this.eval['tl_class']) {
                this.css[this.eval['tl_class']] = true;
            }
            if (this.eval['mandatory']) {
                this.css['mandatory'] = true;
            }
            this.css['error'] = this.eval['validate'] === false;
            this.css[this.name] = true;
            return this.css;
        }
    },
    watch: {
        value: function() {
            this.$emit('input', this.value);
            this.eval['validate'] = true;
            localStorage.setItem('field-' + this.name + '-' + this.idPrefix, this.value);
        }
    },
    mounted: function () {
        setTimeout(function () {
            if (!this.value) {
                this.value = localStorage.getItem('field-' + this.name + '-' + this.idPrefix) ? localStorage.getItem('field-' + this.name + '-' + this.idPrefix) : '';
            }
        }.bind(this), 50);
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
