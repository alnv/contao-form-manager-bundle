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
            objCssClass[this.name] = true;
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
        getStringifyValue: function() {
            return JSON.stringify(this.value);
        },
        setDropzone: function (setValue) {
            if ( typeof Dropzone === 'undefined' ) {
                return null;
            }
            var vueInstance = this;
            var objDropzoneOptions = {
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
                    file.acceptDimensions = done;
                    file.rejectDimensions = function(strMessage) { done(strMessage) };
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
            var objDropzone = new Dropzone(this.$el.querySelector('.dropzone'), objDropzoneOptions);
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
        }
    },
    mounted: function() {
        this.setDropzone(this.setValue);
        this.fetchUploads();

        this.eval.dropHereLabel = this.eval.dropHereLabel ? this.eval.dropHereLabel : 'Drop files here to upload';
        this.eval.deleteLabel = this.eval.deleteLabel ? this.eval.deleteLabel : 'Löschen';
    },
    template:
    '<div class="field-component upload" v-bind:class="setCssClass()">' +
        '<div class="field-component-container">' +
            '<div v-if="files.length" class="files">' +
                '<ul v-for="file in files" class="file">' +
                    '<li v-if="!file.imagesize" class="document"><span>({{ file.path }})</span><div class="controller"><button type="button" v-on:click.prevent="deleteFile(file.uuid,true)"><span v-html="eval.deleteLabel">Löschen</span></button></div></li>' +
                    '<li v-if="file.imagesize" class="image"><figure><img :src="file.path" :alt="file.name"></figure><div class="controller"><button type="button" v-on:click.prevent="deleteFile(file.uuid,true)"><span v-html="eval.deleteLabel">Löschen</span></button></div></li>' +
                '</ul>' +
            '</div>' +
            '<input type="hidden" :name="name" :value="getStringifyValue()">' +
            '<label v-if="eval.label" class="label" v-html="eval.label"></label>' +
            '<div class="dropzone"><div class="dz-message" data-dz-message><span v-html="eval.dropHereLabel"></span></div></div>' +
            '<template v-if="!eval.validate"><p class="error" v-for="message in eval.messages">{{ message }}</p></template>' +
            '<div v-if="eval.description" v-html="eval.description" class="info"></div>' +
        '</div>' +
    '</div>'
});