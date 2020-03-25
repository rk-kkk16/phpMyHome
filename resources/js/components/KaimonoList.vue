<template>
<section>
    <p>総数：{{total}}件</p>

    <table class="table table-bordered">
    <tbody>
        <template v-for="(row, index) in rows">
        <tr>
            <td :class="`star_lv${row.level}`" style="width:5.5em">
                <a href="javascript:void()" @click="toggleState(row.id, row.state)">
                    <i v-if="row.state=='done'" class="far fa-check-square fa-lg" style="background-color:#fff"></i>
                    <i v-else class="far fa-square fa-lg" style="background-color:#fff"></i>
                </a>
                <span
                    v-bind:class="{ 'hidden':!(row.state == 'done' && row.buy_user_id)}"
                    class="profimg prfmini"
                    v-bind:style="`background-image:url(/users/icon/${row.buy_user_id})`">
                </span>
            </td>
            <td>{{row.item_name}}</td>
            <td style="min-width:1em; text-align:right">{{row.quantity}}個</td>
            <td style="width:1.5em">
                <button type="button" class="btn btn-secondary" v-if="row.open==true" @click="rows[index].open=false">▲</button>
                <button type="button" class="btn btn-secondary" v-else @click="rows[index].open=true">▼</button>
            </td>
        </tr>
        <tr v-bind:class="{'hidden':!(row.open==true)}">
            <td :class="`star_lv${row.level}`"></td>
            <td colspan="3" style="text-align:right">
                <button v-bind:onclick="`mwOpen('mw_edit','mw_edit',{id:${row.id}})`" class="btn btn-primary" style="padding:2px 10px"><i class="far fa-edit"></i></button>
                <button v-bind:onclick="`mwOpen('mw_delete','mw_delete',{id:${row.id}})`" class="btn btn-secondary" style="padding:2px 10px"><i class="fas fa-trash"></i></button>
            </td>
        </tr>
        </template>
    </tbody>
    </table>

    <div v-bind:class="{ 'hidden':page_end }">
        <button @click="loadNext();" class="btn btn-link" style="width:100%;">
            <p style="background-color:#cfcfcf; padding:5px; text-align:center">次の{{num}}件</p>
        </button>
    </div>
    <div v-bind:class="{ 'hidden':!page_end }">
        <p style="background-color:#ffffef; padding:5px; text-align:center">全件表示しました。</p>
    </div>
</section>
</template>
<script>
export default {
    data() {
        return {
            total: 0,
            rows: [],
            index: {},
            page: 1,
            page_end: false,
            paramstr: '',
        };
    },

    props: ['num', 'state', 'sort_key', 'sort_order'],

    mounted() {
        $('#loading').removeClass('hidden');
        this.loadNext();
    },

    methods: {
        loadNext() {
            $('#loading').removeClass('hidden');
            let self = this;
            let paramstr = 'num=' + self.num + '&state=' + self.state + '&sort_key=' + self.sort_key + '&sort_order=' + self.sort_order;
            let url = '/api/mustbuys?page=' + self.page + '&' + paramstr;
            axios.get(url).then(function(response) {
                self.total = response.data.meta.total;
                for (var i = 0; i < response.data.data.length; i++) {
                    let row = response.data.data[i];
                    row.open = false;
                    if (typeof(self.index[row.id]) == 'undefined') {
                        self.rows.push(row);
                        self.index[row.id] = row.id;
                    } else {
                        for (var j = 0; j < self.rows.length; j++) {
                            if (self.rows[j].id == row.id) {
                                self.rows[j] = row;
                                break;
                            }
                        }
                    }
                }

                if (response.data.meta.last_page > self.page) {
                    self.page++;
                } else {
                    self.page_end = true;
                }

                $('#loading').addClass('hidden');
            });
        },

        // state切替
        toggleState(id, nowstate) {
            $('#loading').removeClass('hidden');
            let self = this;
            let newstate = '';
            if (nowstate == 'yet') {
                newstate = 'done';
            } else {
                newstate = 'yet';
            }
            let url = '/api/mustbuys/toggle/' + id + '/' + newstate;
            axios.put(url, {}).then(function(response) {
                console.log(response.data.data);
                for (var i = 0; i < self.rows.length; i++) {
                    if (self.rows[i].id == response.data.data.id) {
                        //self.rows[i] = response.data.data;
                        self.rows[i].state = newstate;
                        self.rows[i].buy_user_id = response.data.data.buy_user_id;
                        break;
                    }
                }
            })
            .catch(error => {
                console.error(error);
            });
            $('#loading').addClass('hidden');
        },


        // アイテム追加,更新 親componentから入ってくるデータを適用する
        // @todo: total再計算をどうするか ひとまずそのままにする
        callNewItem(item) {
            if (typeof(this.index[item.id]) != 'undefined') {
                for (var i = 0; i < this.rows.length; i++) {
                    if (this.rows[i].id == item.id) {
                        this.rows[i].item_name = item.item_name;
                        this.rows[i].quantity = item.quantity;
                        this.rows[i].level = item.level;
                        break;
                    }
                }
            } else {
                item.open = false;
                this.rows.unshift(item);
                this.index[item.id] = item.id;
            }
        },

        // アイテム削除 親componentから指定されたidの要素を削除する
        callDeleteItem(item_id) {
            if (typeof(this.index[item_id]) != 'undefined') {
                delete this.index[item_id];
                for (var i = 0; i < this.rows.length; i++) {
                    if (this.rows[i].id == item_id) {
                        this.rows.splice(i, 1);
                        break;
                    }
                }
            }
        },
    }
}
</script>
