Vue.component('upload-field', {
    data: function () {
        return {
            files: []
        }
    },
    methods: {
        fetchUploads: function() {
            if (!this.value || !this.value.length) {
                this.files = [];
                return null;
            }
            this.$http.post('/form-manager/getFiles', {
                files: this.value,
                table: this.eval['_table'],
                fieldname: this.eval['_identifier'],
                pageId: (this.$parent.pageId?this.$parent.pageId:'')
            },{
                emulateJSON: true,
                'Content-Type': 'application/x-www-form-urlencoded'
            }).then(function (objResponse) {
                if (objResponse.body) {
                    this.files = objResponse.body.files;
                    this.$emit('input',this.value);
                }
            }.bind(this));
        },
        setCssClass: function () {
            let objCssClass = {};
            if ( this.eval['tl_class'] ) {
                objCssClass[this.eval['tl_class']] = true;
            }
            objCssClass['mandatory'] = !!this.eval['mandatory'];
            objCssClass['multiple'] = !!this.eval['multiple'];
            objCssClass[this.name] = true;
            return objCssClass;
        },
        setValue: function(uuid) {
            if (!this.value) {
                this.value = [];
            }
            if (this.value.indexOf(uuid) !== -1) {
                return null;
            }
            if (this.eval.multiple) {
                this.value.push(uuid);
            } else {
                this.deleteFiles();
                this.value.push(uuid);
            }
            this.fetchUploads();
        },
        deleteFiles: function() {
            let values = this.value;
            for (let i=0;i<values.length;i++) {
                this.deleteFile(values[i],false);
            }
        },
        deleteFile: function(uuid,fetch) {
            let index = this.value.indexOf(uuid);
            if (index !== -1) {
                this.value.splice(index,1);
                this.$http.post('/form-manager/deleteFile', {
                    file: uuid,
                    table: this.eval['_table'],
                    fieldname: this.eval['_identifier']
                },{
                    emulateJSON: true,
                    'Content-Type': 'application/x-www-form-urlencoded'
                }).then(function () {
                    this.$emit('input',this.value);
                    if (fetch) {
                        this.fetchUploads();
                    }
                }.bind(this));
            }
        },
        getStringifyValue: function() {
            return JSON.stringify(this.value);
        },
        setDropzone: function (setValue) {
            if (typeof Dropzone === 'undefined') {
                return null;
            }
            let vueInstance = this;
            let objDropzoneOptions = {
                url: '/form-manager/upload',
                paramName: this.name,
                parallelUploads: 1,
                params: {
                    identifier: this.eval['_identifier'],
                    source: this.eval['_source'],
                    table: this.eval['_table']
                },
                init: function() {
                    this.on('thumbnail', function(file) {
                        if (vueInstance.eval['allowedWidth'] && vueInstance.eval['allowedWidth'] !== file.width) {
                            file.rejectDimensions(vueInstance.eval['allowedWidthError']);
                            return null;
                        }
                        if (vueInstance.eval['allowedHeight'] && vueInstance.eval['allowedHeight'] !== file.height) {
                            file.rejectDimensions(vueInstance.eval['allowedHeightError']);
                            return null;
                        }
                        file.acceptDimensions();
                    })
                },
                accept: function(file, done) {
                    if (vueInstance.eval['role'] === 'files' || vueInstance.eval['role'] === 'file') {
                        done();
                    }
                    file.acceptDimensions = done;
                    file.rejectDimensions = function(strMessage) {done(strMessage)};
                },
                complete: function () {
                    for (let i=0;i<this.files.length;i++) {
                        if (this.files[i]['status'] !== 'success') {
                            continue;
                        }
                        if (!this.files[i]['xhr']['response']) {
                            continue;
                        }
                        let objResponse = JSON.parse(this.files[i]['xhr']['response']);
                        if (objResponse['file']) {
                            setValue(objResponse['file']['uuid']);
                        }
                    }
                }
            };
            let objDropzone = new Dropzone(this.$el.querySelector('.dropzone'), objDropzoneOptions);
            objDropzone.on('complete',function (file) {
                if (!vueInstance.eval['multiple'] && file['status'] === 'success') {
                    objDropzone.removeFile(file);
                }
            });
        }
    },
    watch: {
        value: function() {
            this.fetchUploads();
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
        },
        dropzoneLabels: {
            default: {
                dictDefaultMessage: 'Dateien zum Hochladen hier ablegen'
            },
            type: Object,
            required: false
        },
        defaultLabels: {
            default: {
                remove: 'Löschen'
            },
            type: Object,
            required: false
        }
    },
    mounted: function() {
        this.setDropzone(this.setValue);
        this.fetchUploads();

        if (this.eval.dropHereLabel) {
            this.dropzoneLabels.dictDefaultMessage = this.eval.dropHereLabel;
        }
        if (this.eval.deleteLabel) {
            this.defaultLabels.remove = this.eval.deleteLabel;
        }
    },
    template:
    '<div class="field-component upload" v-bind:class="setCssClass()">' +
        '<div class="field-component-container">' +
            '<div v-if="files.length" class="files">' +
                '<ul v-for="file in files" class="file">' +
                    '<li v-if="!file.imagesize" class="document"><a v-if="file.href" target="_blank" :href="file.href">{{ file.name }}</a><span v-if="!file.href">{{ file.name }}</span><div class="controller"><button type="button" v-on:click.prevent="deleteFile(file.uuid,true)"><span v-html="defaultLabels.remove"></span></button></div></li>' +
                    '<li v-if="file.imagesize" class="image">' +
                        '<figure>' +
                            '<a target="_blank" :href="file.href">' +
                                '<img :src="file.path" :alt="file.name">' +
                            '</a>'+
                        '</figure>' +
                        '<div class="controller"><button type="button" v-on:click.prevent="deleteFile(file.uuid,true)"><span v-html="defaultLabels.remove"></span></button></div>' +
                    '</li>' +
                '</ul>' +
            '</div>' +
            '<input type="hidden" :name="name" :value="getStringifyValue()">' +
            '<label v-if="eval.label" class="label" v-html="eval.label"></label>' +
            '<div class="dropzone"><div class="dz-message" data-dz-message><span v-html="dropzoneLabels.dictDefaultMessage"></span></div></div>' +
            '<template v-if="!eval.validate"><p class="error" v-for="message in eval.messages">{{ message }}</p></template>' +
            '<div v-if="eval.description" v-html="eval.description" class="info"></div>' +
        '</div>' +
    '</div>'
});