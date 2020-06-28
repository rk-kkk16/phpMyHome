<template>
<section id="dash-goodpost">
    <div class="card">
        <div class="card-header"><a href="/goodpost">イイヨ！！</a></div>
        <div class="card-body">
            <div id="dash-gop-loading" style="text-align:center;width:60%;"><img src="/css/loading.gif"></div>
            <div v-if="post" class="card" :class="{'hidden':!load_end}">
                <div class="card-header">
                    <span class="profimg prfmini" v-bind:style="`background-image:url(/users/icon/${post.user.id})`"></span> {{post.user.name}}
                    から
                    <span class="profimg prfmini" v-bind:style="`background-image:url(/users/icon/${post.toUser.id})`"></span> {{post.toUser.name}}
                    へ
                </div>
                <div class="card-body">
                    <div id="good-pallet-dash" class="good-pallet">
                        <div class="good-txt" v-html="$options.filters.nl2br(post.body)"></div>
                        <div v-for="heart in hearts" class="good-heart-mini" v-bind:style="`top:${heart.top}%; left:${heart.left}%; z-index:${heart.zind};`">{{heart_str}}</div>
                    </div>
                    <br>
                    <p>
                        <span class="good-heart">{{heart_str}}</span>
                        × {{post.total_good}}
                    </p>
                    <p style="text-align:right"><i class="fas fa-calendar-alt"></i> {{post.created_at|moment}}</p>
                </div>
            </div>
            <p v-else>投稿はありません。</p>

            <br>
            <p style="text-align:right"><a href="/goodpost">&gt;&gt;イイヨ！！へ</a></p>
        </div>
    </div>
</section>
</template>
<script>
import moment from 'moment';
export default {
    filters: {
        moment: function (date) {
            return moment(date).format('YYYY/MM/DD HH:mm');
        }
    },

    data() {
        return {
            heart_str: '💞',
            post: null,
            hearts: [],
            load_end: false,
        };
    },

    mounted() {
        let self = this;
        let url = '/api/goodpost/latest';
        axios.get(url)
        .then(function(response) {
            self.post = response.data.data;
            $('#dash-gop-loading').hide();
            self.load_end = true;
            // 200510 現時点でgood-palletの高さをうまく自動調整できないのでハート散りばめ一旦オフ
            //self.setGoodHeart(self.post.total_good);
        })
        .catch(function(error) {
            $('#dash-gop-loading').hide();
            self.load_end = true;
        });
    },

    methods: {
        setGoodHeart(total_good) {
            let zIndexBase = 100;
            for (var i = 0; i < total_good; i++) {
                let zind = zIndexBase + i;
                let h_x = Math.floor(Math.random() * 100);
                let h_y = Math.floor(Math.random() * 100);
                this.hearts.push({top:h_y, left:h_x, zind:zind});
            }
        }
    }
}
</script>
<style scoped>
.good-pallet>.good-txt {
    position:relative;
}
</style>
