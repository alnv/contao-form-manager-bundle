Vue.component( 'explain-field', {
    data: function () {
        return {}
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
            if ( this.eval['class'] ) {
                objCssClass[this.eval['class']] = true;
            }
            objCssClass[this.name] = true;
            return objCssClass;
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
        '<div class="field-component explain" v-bind:class="setCssClass()">' +
            '<div class="field-component-container" v-html="eval.text"></div>' +
        '</div>'
});
