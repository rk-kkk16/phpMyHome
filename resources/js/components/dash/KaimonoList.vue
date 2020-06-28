<template>
<section id="dash-kaimono" class="card">
    <div class="card-header"><a href="/kaimono">買い物リスト</a></div>
    <div class="card-body">
        <div id="dash-kaimono-loading" style="text-align:center;width:60%;"><img src="/css/loading.gif"></div>
        <table class="table table-bordered"><tbody>
        <template v-if="rows.length > 0">
            <tr v-for="row in rows">
                <td :class="`star_lv${row.level}`" style="width:3em">
                    <a href="javascript:void()" @click="klToggleState(row.id, row.state)">
                        <i v-if="row.state=='done'" class="far fa-check-square fa-lg" style="background-color:#fff"></i>
                        <i v-else class="far fa-square fa-lg" style="background-color:#fff"></i>
                    </a>
                </td>
                <td>{{row.item_name}}</td>
                <td style="min-width:1em; text-align:right">{{row.quantity}}個</td>
            </tr>
        </template>
        <template v-else>
            <tr><td>買わなきゃ...はありません。</td></tr>
        </template>
        </tbody></table>
        <p style="text-align:right"><a href="/kaimono">&gt;&gt;買い物リストへ</a></p>
    </div>
</section>
</template>
<script>
export default {
    data() {
        return {
            rows: [],
            index: {},
        };
    },
    props: [],
    mounted() {
        let self = this;
        let params = 'num=5&state=yet&sort_key=level&sort_order=desc';
        let url = '/api/mustbuys?page=1&' + params;
        axios.get(url).then(function(response) {
            $('#dash-kaimono-loading').hide();
            for (var i = 0; i < response.data.data.length; i++) {
                let row = response.data.data[i];
                if (typeof(self.index[row.id]) == 'undefined') {
                    self.rows.push(row);
                    self.index[row.id] = row.id;
                }
            }
        });
    },

    methods: {
        // state切替
        klToggleState(id, nowstate) {
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
        },
    }
}
</script>
