Vue.component( 'textarea-field', {
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
            objCssClass[this.name] = true;
            return objCssClass;
        },
        loadTinymce: function () {
            const objTinyScriptTag = document.getElementById('tinyMCE');
            if (objTinyScriptTag) {
                return null;
            }
            let vue = this;
            let objScript = document.createElement('script');
            objScript.id = 'tinyMCE';
            objScript.onload = function () {
                const strId = this.idPrefix + 'id_' + this.name;
                tinymce.init({
                    selector: '#' + strId,
                    setup: function(editor) {
                        editor.on('keyup', function () {
                            vue.value = this.getContent();
                        });
                    }
                });
            }.bind(this);
            objScript.src = 'bundles/alnvcontaoformmanager/js/libs/tinymce/tinymce.min.js';
            document.head.appendChild(objScript);
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
    mounted: function() {
        if (this.eval.hasOwnProperty('rte') && this.eval['rte'] === 'tinyMCE') {
            this.loadTinymce();
        }
    },
    template:
    '<div class="field-component textarea" v-bind:class="setCssClass()">' +
        '<div class="field-component-container">' +
            '<label class="label" :for="idPrefix + \'id_\' + name" v-html="eval.label"></label>' +
            '<textarea v-if="eval.rte" :id="idPrefix + \'id_\' + name" v-html="value"></textarea>' +
            '<textarea v-if="!eval.rte" :id="idPrefix + \'id_\' + name" v-model="value"></textarea>' +
            '<template v-if="!eval.validate"><p class="error" v-for="message in eval.messages">{{ message }}</p></template>' +
            '<div v-if="eval.description" v-html="eval.description" class="info"></div>' +
        '</div>' +
    '</div>'
});
