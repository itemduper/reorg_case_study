<template>
    <div class="Typeahead">
        <font-awesome-icon :icon="['fas', 'spinner']" spin v-if="loading" />
        <template v-else>
            <font-awesome-icon :icon="['fas', 'search']" v-if="isEmpty" />
            <font-awesome-icon :icon="['fas', 'times']" v-if="isDirty" @click="resetSearch" />
        </template>

        <input type="text"
                class="Typeahead__input"
                placeholder="Enter the name of a Physician or Hospital"
                autocomplete="off"
                v-model="query"
                @keydown.down="down"
                @keydown.up="up"
                @keydown.enter="hit"
                @keydown.esc="reset"
                @blur="reset"
                @input="update"/>

        <ul v-show="hasItems">
            <li v-for="(item, $item) in items" :key="item.id" :class="activeClass($item)" @mousedown="hit" @mousemove="setActive($item)">
                <span v-text="item.name"></span>
            </li>
        </ul>
    </div>
</template>

<script>
    import VueTypeahead from 'vue-typeahead';

    export default {
        extends: VueTypeahead,
        data() {
            return {
                /**
                 * API endpoint used by the typeahead to query payment recipients.
                 */
                src: '/api/payment/recipient/search',
                /**
                 * Limit to typeahead results.
                 */
                limit: 10,
                /**
                 * Minimum characters before the typeahead starts querying the API.
                 */
                minChars: 3,
                /**
                 * Tells the Typeahead to automatically select the first result.
                 */
                selectFirst: true,
            }
        },
        methods: {
            /**
             * Triggered when an item is selected from the typeahead.
             * 
             * @param {Object} item Item selected from the typeahead.
             * 
             * @event selected Notifies parent component that an item was selected.
             * @property {Object} item item selected from the typeahead.
             */
            onHit(item) {
                $('.Typeahead__input').blur();
                this.query = item.name;
                this.$emit('selected', item);
            },

            /**
             * Resets the typeahead.
             * 
             * @event reset Notifies parent component the typeahead was reset.
             */
            resetSearch() {
                this.reset();
                this.$emit('reset');
            }
        },
        computed: {

        },
        mounted() {

        }
    }
</script>

<style scoped>
    .Typeahead {
        position: relative;
        width: 100%;
    }
    .Typeahead__input {
        width: 100%;
        font-size: 14px;
        color: #2c3e50;
        line-height: 1.42857143;
        box-shadow: inset 0 1px 4px rgba(0,0,0,.4);
        -webkit-transition: border-color ease-in-out .15s,-webkit-box-shadow ease-in-out .15s;
        transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s;
        font-weight: 300;
        padding: 12px 26px;
        border: none;
        letter-spacing: 1px;
        box-sizing: border-box;
    }
    .Typeahead__input:focus {
        border-color: #000;
        outline: 0;
        box-shadow: inset 0 1px 1px rgba(0,0,0,.075),0 0 8px #777777;
    }
    .fa-times {
        cursor: pointer;
    }
    svg {
        float: right;
        position: relative;
        top: 30px;
        right: 29px;
        opacity: 0.4;
    }
    ul {
        position: absolute;
        padding: 0;
        margin-top: 8px;
        min-width: 100%;
        background-color: #fff;
        list-style: none;
        border-radius: 4px;
        box-shadow: 0 0 10px rgba(0,0,0, 0.25);
        z-index: 1000;
    }
    li {
        padding: 10px 16px;
        border-bottom: 1px solid #ccc;
        cursor: pointer;
    }
    li:first-child {
        border-top-left-radius: 4px;
        border-top-right-radius: 4px;
    }
    li:last-child {
        border-bottom-left-radius: 4px;
        border-bottom-right-radius: 4px;
        border-bottom: 0;
    }
    span {
        display: block;
        color: #000;
    }
    .active {
        background-color: #777777;
    }
    .active span {
        color: white;
    }
    .name {
        font-weight: 700;
        font-size: 18px;
    }
</style>