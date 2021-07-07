<template>
    <ve-table id="payments-table" :columns="cols" :table-data="tableData" :sort-option="sortOption" :border-x="true" :border-y="true" />
</template>

<script>
    export default {
        props: {
            cols: Array,
            rows: Array,
        },
        data() {
            return {
                /**
                 * Parameters to sort the table's columns with.
                 */
                sortParams: null,
                /**
                 * Sort Option object used in vue-easytable
                 */
                sortOption: {
                    sortChange: (params) => {
                        this.sortParams = params;
                    },
                },
            }
        },
        methods: {

        },
        computed: {
            /**
             * Rows to include in the table sorted by the sortParams.
             */
            tableData() {
                let data = this.rows;

                if(this.sortParams !== null) {
                    Object.keys(this.sortParams).forEach(col => {
                        if(this.sortParams[col]) {
                            data.sort((a, b) => {
                                if(this.sortParams[col] == 'asc') {
                                    return (a[col] > b[col]) ? 1 : -1;
                                } else if(this.sortParams[col] == 'desc') {
                                    return (a[col] < b[col]) ? 1 : -1;
                                } else {
                                    return 0;
                                }
                            });
                        }
                    });
                }

                return data;
            }
        },
        mounted() {

        }
    }
</script>

<style>
    #payments-table {
        width: 98%;
        margin-left: auto;
        margin-right: auto;
    }

    span.ve-table-sort {
        line-height: 1;
    }

    th.ve-table-header-th {
        white-space: nowrap;
    }
</style>