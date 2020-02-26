const tableListComponent = Vue.component( 'table-list', {
    data: function () {
        return {
            list: [],
            fields: [],
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
                this.fields = objResponse.body.fields;
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
            this.$http.post( item.operations.delete.href,
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
        addButtonPosition: {
            type: String,
            required: false,
            default: 'before'
        }
    },
    template:
        '<div class="table-list-component">' +
            '<div class="table-list-component-container">' +
                '<div v-if="addUrl && addButtonPosition === \'before\'" class="operator add">' +
                    '<slot name="add" v-bind:addUrLabel="addUrlLabel" v-bind:addUrl="addUrl">' +
                        '<a :href="addUrl" :title="addUrlLabel" v-html="addUrlLabel"></a>' +
                    '</slot>' +
                '</div>' +
                '<div v-if="list.length" class="table">' +
                    '<div class="thead">' +
                        '<div class="tr">' +
                            '<slot name="th" v-bind:fields="fields" v-bind:labels="labels">' +
                                '<div v-for="field in fields" class="th" v-bind:class="setFieldCssClass(field)" v-html="labels[field]"></div>' +
                                '<div v-if="operations.length" class="th operations" v-html="labels[\'operations\']"></div>' +
                            '</slot>' +
                        '</div>' +
                    '</div>'+
                    '<div class="tbody">' +
                        '<div class="tr" v-for="item in list">' +
                            '<slot name="td" v-bind:fields="fields" v-bind:callOperator="callOperator" v-bind:operations="operations" v-bind:item="item">' +
                                '<div v-for="field in fields" class="td" v-bind:class="setFieldCssClass(field)" v-html="item[field]"></div>' +
                                '<div v-if="operations.length" class="td operations">' +
                                    '<div v-bind:class="setOperatorCssClass(operator)" v-for="operator in operations" v-if="item.operations[operator][\'href\']">' +
                                        '<a v-on:click="callOperator($event,operator,item)" :href="item.operations[operator][\'href\']" :title="item.operations[operator][\'label\']" v-html="item.operations[operator][\'label\']"></a>' +
                                    '</div>' +
                                '</div>' +
                            '</slot>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
                '<div v-if="addUrl && addButtonPosition === \'after\'" class="operator add">' +
                    '<slot name="add" v-bind:addUrLabel="addUrlLabel" v-bind:addUrl="addUrl">' +
                        '<a :href="addUrl" :title="addUrlLabel" v-html="addUrlLabel"></a>' +
                    '</slot>' +
                '</div>' +
            '</div>' +
        '</div>'
});