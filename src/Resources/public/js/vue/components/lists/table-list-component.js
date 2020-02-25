const tableListComponent = Vue.component( 'table-list', {
    data: function () {
        return {
            list: [],
            labels: {}
        }
    },
    methods: {
        fetch: function () {
            this.$parent.setLoadingAlert('', this);
            this.$http.post( '/form-manager/list-view',
                {
                    module: this.module
                },
                {
                emulateJSON: true,
                'Content-Type': 'application/x-www-form-urlencoded'
                }
            ).then( function (objResponse) {
                this.list = objResponse.body.list;
                this.labels = objResponse.body.labels;
                if (objResponse.body.success) {
                    this.$parent.clearAlert();
                } else {
                    this.$parent.setErrorAlert('', this);
                }
            });
        },
        setOperatorCssClass: function (operator) {
            var objCssClass = {};
            objCssClass['operator'] = true;
            objCssClass[operator] = true;
            return objCssClass;
        },
        setFieldCssClass: function (field) {
            var objCssClass = {};
            objCssClass['field'] = true;
            objCssClass[field] = true;
            return objCssClass;
        },
        deleteItem: function(item) {
            if ( !confirm(this.deleteConfirmLabel) ) {
                return null;
            }
            this.$parent.setLoadingAlert('', this);
            this.$http.post( '/form-manager/deleteItem/' + item.id,
                {
                    module: this.module,
                },
                {
                    emulateJSON: true,
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            ).then( function (objResponse) {
                if (objResponse.body.success && objResponse.ok) {
                    for (var i = 0; i < this.list.length; i++) {
                        if (this.list[i] === item) {
                            this.list.splice(i,1);
                        }
                    }
                    if (!this.list.length) {
                        this.disableLoader = true;
                    }
                    this.$parent.clearAlert();
                } else {
                    this.$parent.setErrorAlert('', this);
                }
            });
        },
        callOperator: function ($e,operator,item) {
            switch (operator) {
                case 'delete':
                    $e.preventDefault();
                    this.deleteItem(item);
                    return false;
                default:
                    return null;
            }
        }
    },
    mounted: function () {
        this.fetch();
    },
    props: {
        module: {
            type: String,
            default: null,
            required: true
        },
        fields: {
            type: Array,
            required: false,
            default: ['title','operations']
        },
        addUrl: {
            type: String,
            required: false,
            default: ''
        },
        addUrlLabel: {
            type: String,
            required: false,
            default: ''
        },
        deleteConfirmLabel: {
            type: String,
            required: false,
            default: 'Sind Sie sicher, dass Sie Ihren Eintrag löschen wollen?'
        },
        operations: {
            type: Array,
            required: false,
            default: ['edit','delete']
        },
        disableLoader: {
            type: Boolean,
            default: false,
            required: false
        },
    },
    template:
        '<div class="table-list-component">' +
            '<div class="table-list-component-container">' +
                '<div v-if="addUrl" class="operator add">' +
                    '<a :href="addUrl" :title="addUrlLabel" v-html="addUrlLabel"></a>' +
                '</div>' +
                '<div v-if="list.length" class="table">' +
                    '<div class="thead">' +
                        '<div class="tr">' +
                            '<div class="th" v-bind:class="setFieldCssClass(field)" v-for="field in fields" v-html="labels[field]"></div>' +
                        '</div>' +
                    '</div>'+
                    '<div class="tbody">' +
                        '<div class="tr" v-for="item in list">' +
                            '<div v-if="field!==\'operations\'" class="td" v-bind:class="setFieldCssClass(field)" v-for="field in fields" v-html="item[field]"></div>' +
                            '<div class="td" v-else class="td operations">' +
                                '<div v-bind:class="setOperatorCssClass(operator)" v-for="operator in operations">' +
                                    '<a v-on:click="callOperator($event,operator,item)" :href="item.operations[operator][\'href\']" :title="item.operations[operator][\'label\']" v-html="item.operations[operator][\'label\']"></a>' +
                                '</div>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
            '</div>' +
        '</div>'
});