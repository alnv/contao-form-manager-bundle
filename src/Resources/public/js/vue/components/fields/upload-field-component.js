Vue.component( 'upload-field', {
    data: function () {
        return {
            files: []
        }
    },
    methods: {
        fetchUploads: function() {
            if ( !this.value || !this.value.length ) {
                this.files = [];
                return null;
            }
            this.$http.post( '/form-manager/getFiles', {
                files: this.value,
                table: this.eval['_table'],
                fieldname: this.eval['_identifier']
            },{
                emulateJSON: true,
                'Content-Type': 'application/x-www-form-urlencoded'
            }).then(function ( objResponse ) {
                if ( objResponse.body ) {
                    this.files = objResponse.body.files;
                }
            });
        },
        setCssClass: function () {
            let objCssClass = {};
            if ( this.eval['tl_class'] ) {
                objCssClass[this.eval['tl_class']] = true;
            }
            objCssClass['mandatory'] = !!this.eval['mandatory'];
            objCssClass['multiple'] = !!this.eval['multiple'];
            return objCssClass;
        },
        setValue: function(uuid) {
            if ( !this.value ) {
                this.value = [];
            }
            if ( this.value.indexOf(uuid) !== -1 ) {
                return null;
            }
            if ( this.multiple ) {
                this.value.push(uuid);
            } else {
                this.deleteFiles();
                this.value.push(uuid);
            }
            this.fetchUploads();
        },
        deleteFiles: function() {
            var values = this.value;
            for (var i=0;i<values.length;i++) {
                this.deleteFile(values[i],false);
            }
        },
        deleteFile: function(uuid,fetch) {
            var index = this.value.indexOf(uuid);
            if (index !== -1) {
                this.value.splice(index,1);
                this.$http.post( '/form-manager/deleteFile', {
                    file: uuid,
                    table: this.eval['_table'],
                    fieldname: this.eval['_identifier']
                },{
                    emulateJSON: true,
                    'Content-Type': 'application/x-www-form-urlencoded'
                }).then(function () {
                    if (fetch) {
                        this.fetchUploads();
                    }
                });
            }
        },
        setDropzone: function (setValue) {
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
                complete: function () {
                    for (var i=0;i<this.files.length;i++) {
                        if (this.files[i]['status'] !== 'success' ) {
                            continue;
                        }
                        if ( !this.files[i]['xhr']['response'] ) {
                            continue;
                        }
                        var objResponse = JSON.parse(this.files[i]['xhr']['response']);
                        if ( objResponse['file'] ) {
                            setValue(objResponse['file']['uuid']);
                        }
                    }
                }
            };
            var objDropzone = new Dropzone(this.$el.querySelector('.dropzone'),objDropzoneOptions);
            objDropzone.on('complete',function (file) {
                if (file['status'] !== 'success') {
                    file.previewElement.addEventListener('click', function() {
                        objDropzone.removeFile(file);
                    });
                }
            });
        }
    },
    watch: {
        value: function() {
            this.$emit('input',this.value);
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
            default: [],
            type: Array,
            required: false
        }
    },
    mounted: function() {
        this.setDropzone(this.setValue);
        this.fetchUploads();
    },
    template:
    '<div class="field-component upload" v-bind:class="setCssClass()">' +
        '<div class="field-component-container">' +
            '<div v-if="files.length" class="files">' +
                '<div class="files-container">' +
                    '<ul v-for="file in files" class="file">' +
                        '<li><span class="name">{{ file.name }} <span>({{ file.path }})</span></span><span class="controller"><button v-on:click.prevent="deleteFile(file.uuid,true)">Bild entfernen</button></span></li>' +
                    '</ul>' +
                '</div>' +
            '</div>' +
            '<input type="hidden" v-model="value">' +
            '<label class="label">{{ eval.label }}</label>' +
            '<div class="dropzone"></div>' +
            '<template v-if="!eval.validate"><p class="error" v-for="message in eval.messages">{{ message }}</p></template>' +
            '<template v-if="eval.description"><p class="description">{{ eval.description }}</p></template>' +
        '</div>' +
    '</div>'
});