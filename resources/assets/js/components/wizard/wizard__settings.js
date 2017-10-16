App.Components.WizardSettings = {
    template: "#wizard__settings-template",
    data: function () {
        return {
            user: {},
            chains: [],
            user_id: "",
            success: {},
            errors: {},
            loaded: false,
            chainName: '',
            chainDays: '',
            chainIcon: '',
            iconBackground: {
                'bg-red': false, 'bg-blue': false, 'bg-maroon': false, 'bg-green': false, 'bg-yellow': false,
                'bg-grey': false, 'bg-purple' : false, 'fa-calendar' : false, 'fa-heart' : false,
                'fa-asterisk' : false, 'fa-bookmark' : false, 'fa-eye' : false, 'fa-flag' : false,
                'fa-medkit' : false, 'fa-paw' : false, 'fa-trophy' : false, 'fa-inbox' : false
            },
            loading: false,
            step: 1,
			supports_notifications: false,
            notifications_disabled: false,
            notifications_subscribed: false
        }
    },
    props: [],
    components: {
        'settings-image-upload': App.Components.SettingsImageUpload
    },
    computed: {
        isSubscribed: function() {
            return App.isSubscribed;
        },
        isPremium: function() {
            return App.isPremiumSubscribed;
        }
    },
    watch:{

        'chainIcon': function(newValue, oldValue){

            if(newValue){
                var newClass = newValue.split(' ')[1];
                var oldClass = oldValue ? oldValue.split(' ')[1] : '';

                if(newClass == 'bg-gray') newClass = newValue.split(' ')[0];
                if(oldClass == 'bg-gray') oldClass = oldValue ? oldValue.split(' ')[0] : '';

                this.iconBackground[newClass] = true;
                if(oldValue) this.iconBackground[oldClass] = false;

            }
        },

        'digest_enabled': function(newValue, oldValue){
            $(this.$els.digest_enabled).iCheck('update');
            if(this.loaded) {
                if (newValue == false) {
                    this.temp_digest_day = this.user.digest_day;
                    this.user.digest_day = -1;
                } else {
                    this.user.digest_day = this.temp_digest_day;
                }
            }
        },

        'step' : function(newValue, oldValue){
            if(newValue == 5) {
                addtohome.show();
            } else if(newValue == 4) {
                if(!this.supports_notifications) {
                  this.step = 5;
                }
            }
        }
    },
    methods: {
        updateSettings: function updateSettings() {
            var _this = this;

            this.success = {};
            this.errors = {};
            this.loading = true;
            api.postUserSettings(this.user.id, this.user).then(function (res) {
                _this.loading = false;
                _this.success = res;
                if (_this.step == 4 && !App.allmobiles()) {
                    _this.$route.router.go('/wizard/breeders');
                } else if (_this.step == 5) {
                    _this.$route.router.go('/wizard/breeders');
                } else {
                    _this.step = _this.step + 1;
                }
            }, function (response) {
                _this.loading = false;
                _this.errors = response.errors;
            });
        },
        skipSettings: function skipSettings() {
            var _this = this;
            if (_this.step == 4 && !App.allmobiles()) {
                _this.$route.router.go('/wizard/breeders');
            } else if (_this.step == 5) {
                _this.$route.router.go('/wizard/breeders');
            } else {
                _this.step = _this.step + 1;
            }
          
        },
        addChain: function () {
            if(!this.chainName) {
                alert('You must specify Name');
                return false;
            }
            if(!this.chainDays) {
                alert('You must specify Days');
                return false;
            }
            var chain = {
                name: this.chainName,
                days: this.chainDays,
                icon: this.chainIcon,
                id: 'new_' + new Date().getTime()
            };

            this.chains.push(chain);
            this.chains = _.sortBy(this.chains, 'days');

            $('#new_chain').modal('hide');
            this.chainName = '';
            this.chainDays = '';


        },

        removeChain: function(id){
            $('li.' + id).remove();
            delete this.user.breedchains.icon[id];
            delete this.user.breedchains.days[id];
            delete this.user.breedchains.name[id];
            this.chains = this.chains.filter(function(chain) {
                return chain.id != id;
            });

        },
		
		updateNotificationManagerState() {
            const manager = window.NotificationManager;

            manager.isAvailable().then(available => {
                this.supports_notifications = available;
            });
            manager.isDisabled().then(disabled => {
                this.notifications_disabled = disabled;
            });
            manager.isSubscribed().then(subscribed => {
                this.notifications_subscribed = subscribed;
            });
        },
        disableNotifications() {
            NotificationManager.unsubscribe(() => {
                this.updateNotificationManagerState();
            });
        },
        subscribeToNotifications() {
            NotificationManager.subscribe(() => {
                this.updateNotificationManagerState();
            }, () => {
                this.updateNotificationManagerState();
            })
        },
        sendTestNotification() {
            api.requestTestNotification();
        }
    },
    ready: function () {
        api.getCurrentUserSettingsData().then(data => {
            this.user = data.user;
            this.user.pedigree_number_generations = 4;
            this.chains = data.chains;
            this.digest_enabled = this.user.digest_day != -1;

            this.$nextTick(function () {
                this.loaded = true;
            })
        });

        $(this.$els.digest_enabled).iCheck({
            checkboxClass: 'icheckbox_square-blue',
            radioClass: 'iradio_square-blue',
            increaseArea: '20%' // optional
        }).on('ifChecked', function(event){
            this.digest_enabled = true;
        }.bind(this))
        .on('ifUnchecked', function(event){
            this.digest_enabled = false;
        }.bind(this));
		
		this.updateNotificationManagerState();
        setInterval(() => { this.updateNotificationManagerState(); }, 1000);
    }
};
