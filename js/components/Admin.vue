<template>
  <div class='app-content'>
      <p>Hello ugly World</p>
      {{ text }}
  </div>
</template>

<script>
//    import Header from './Header.vue'
//    import Footer from './Footer.vue'
//    import Main from './Main.vue'

    export default {
        data: function() {
          return {
              text: "Hello"
            //app: this.$root.app,
          };
        },
//        components: {
//            "app-header": Header,
//            "app-main": Main,
//            "app-footer": Footer,
//        },
        mounted() {
//            this.$store.dispatch('me');
            console.log('App Component mounted.')
        },
//        watch: {
//            '$store.state.auth.guest': function(newval) {
//                console.log('App auth guard');
//                // check that the current route matches our guest/non-guest setting
//                if (this.$route.matched.some(record => record.meta.auth)) {
//                    console.log('route requires auth');
//                    if(!newval) {
//                        console.log('auth state is false, redirecting to login');
//                        this.$router.push({
//                            path: '/login',
//                            query: { redirect: this.$route.fullPath }
//                        });
//                    }
//                }
//                if (this.$route.matched.some(record => record.meta.auth)) {
//                    console.log('route requires guest');
//                    if(newval) {
//                        console.log('auth state is true, redirecting to homepage');
//                        this.$router.push('/');
//                    }
//                }
//            }
//        }
    }
</script>