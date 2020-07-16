<template>
<div id="area-goodpoint">
    <div id="goodpoint-score">
        <span class="thumb">{{thumbs[0]}}</span> × {{total_point}}
    </div>
    <div id="goodpoint-yet" v-if="pushed_today!=true">
        <button class="btn btn-primary" @click="addThumb()" style="padding:3px 3px">＋ {{thumbs[0]}}</button>
    </div>
    <div id="goodpoint-done" v-else>
        <span class="profimg prfmini" v-bind:style="`background-image:url(/users/icon/${user_id});vertical-align:middle;`"></span> ＋ {{thumbs[0]}}
    </div>
    <br clear="both">
</div>
</template>
<script>
export default {
    data() {
        return {
            total_point: 0,
            pushed_today: false,
        };
    },

    props: ['user_id', 'thumbs', 'post_id'],

    mounted() {
        this.loadStatus();
    },

    methods: {
        loadStatus() {
            $('#loading').removeClass('hidden');
            let self = this;
            let url = '/api/scrap/goodpoint/status/' + this.post_id;
            axios.get(url)
            .then(function(response) {
                $('#loading').addClass('hidden');
                if (typeof(response.data) != 'undefined') {
                    self.total_point = response.data.total_point;
                    self.pushed_today = (response.data.pushed_today);
                }
            })
            .catch(function(error) {
                $('#loading').addClass('hidden');
            });
        },

        addThumb() {
            $('#loading').removeClass('hidden');
            let self = this;
            let url = '/api/scrap/goodpoint/add/' + this.post_id;
            axios.put(url)
            .then(function(response) {
                $('#loading').addClass('hidden');
                if (typeof(response.data.errors) != 'undefined') {
                    toastr.error(response.data.errors.join("\n"));
                }
                else if (typeof(response.data) != 'undefined') {
                    let data = response.data;
                    self.total_point = data.total_point;
                    self.pushed_today = (data.pushed_today);
                }
            })
            .catch(function(error) {
                $('#loading').addClass('hidden');
            });
        },
    },
}
</script>
<style scoped>
.thumb {
    font-size: 1.2em;
}
#goodpoint-score {
    float: left;
    margin-right: 1em;
}
#goodpoint-yet {
    float: left;
}
#goodpoint-done {
    float: left;
}
</style>
