Vue.component( 'upload-field', {
    data: function () {
        return {
            //
        }
    },
    methods: {
        setCssClass: function () {
            let objCssClass = {};
            if ( this.eval['tl_class'] ) {
                objCssClass[this.eval['tl_class']] = true;
            }
            objCssClass['mandatory'] = !!this.eval['mandatory'];
            objCssClass['multiple'] = !!this.eval['multiple'];
            return objCssClass;
        },
        setDropzone: function () {
            if ( typeof Dropzone === 'undefined' ) {
                return null;
            }
            var objDropzoneOptions = {
                url: '/form-manager/upload',
                paramName: this.name,
                parallelUploads: 1,
                params: {
                    identifier: this.eval['_identifier'],
                    source: this.eval['_source'],
                    table: this.eval['_table']
                },
                /*
                success: function () {
                    for (var i=0;i<this.files.length;i++) {
                       console.log(this.files[i])
                    }
                    return this;
                },
                error: function () {
                    for (var i=0;i<this.files.length;i++) {
                        console.log(this.files[i])
                    }
                    return this;
                }
                */
            };
            if ( !this.eval.multiple ) {
                objDropzoneOptions.maxFiles = 1;
            }
            new Dropzone(this.$el.querySelector('.dropzone'), objDropzoneOptions);
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
        }
    },
    updated: function () {
        this.$nextTick(function () {
            this.setDropzone();
        })
    },
    mounted: function() {
        this.setDropzone();
    },
    template:
    '<div class="field-component upload" v-bind:class="setCssClass()">' +
        '<div class="field-component-container">' +
            '<label class="label">{{ eval.label }}</label>' +
            '<div class="dropzone"></div>' +
            '<template v-if="!eval.validate"><p class="error" v-for="message in eval.messages">{{ message }}</p></template>' +
            '<template v-if="eval.description"><p class="description">{{ eval.description }}</p></template>' +
        '</div>' +
    '</div>'
});