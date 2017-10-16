// Global app eVents
App.vent = $('body');

App.init = function () {

    if (window.location.hash == '#_=_') {
        window.location.hash = '#';
    }

    Vue.use(VueResource);
    Vue.use(VueRouter);
    Vue.use(VueValidator);

    Vue.filter('caseInsensitiveOrderBy', function (arr, sortKey, reverse) {
        // arr = convertArray(arr)
        if (!sortKey) {
            return arr
        }
        var order = (reverse && reverse < 0) ? -1 : 1
        // sort on a copy to avoid mutating original array
        return arr.slice().sort(function (a, b) {
            if (sortKey !== '$key') {
                if (Vue.util.isObject(a) && '$value' in a) a = a.$value
                if (Vue.util.isObject(b) && '$value' in b) b = b.$value
            }
            a = Vue.util.isObject(a) ? Vue.parsers.path.getPath(a, sortKey) : a
            b = Vue.util.isObject(b) ? Vue.parsers.path.getPath(b, sortKey) : b

            a = a.toLowerCase()
            b = b.toLowerCase()

            return a === b ? 0 : a > b ? order : -order
        })
    });
    
    Vue.http.headers.common['X-CSRF-TOKEN'] = $('meta[name="csrf-token"]').attr('content');

    var router = new VueRouter();
    router.afterEach(function(transition) {
        if (transition.from.path) {
            $('#app-error,#app-success').fadeOut();
        }
    });

    router.beforeEach(function (transition) {
        if (!App.isSubscribed && transition.to.path !== '/account'
                || !App.isPremiumSubscribed && transition.to.path.indexOf('/ledger') == 0) {
            transition.abort();
            router.go('/account');
        } else {
            transition.next();
        }
    });
    router.afterEach(function (transition) {
        ga('send', {
            hitType: 'pageview',
            page: transition.to.path
        })
    });

    router.map({
        '/': {
            component: App.Components.Dashboard
        },
        '/breeders': {
            component: App.Components.Breeders
        },
        '/breeders/categories': {
            component: App.Components.BreederCategories
        },
        '/breeders/:action': {
            name: 'breeders',
            component: App.Components.Breeders
        },
        '/litters': {
            component: App.Components.Litters
        },
        '/litters/:action': {
            name: 'litters',
            component: App.Components.Litters
        },
        '/profile/:id': {
            component: App.Components.Profile
        },
        '/pedigree/:id': {
            component: App.Components.Pedigree
        },
        '/pedigree/kit/:id': {
            component: App.Components.KitPedigree
        },
        '/litterprofile/:id': {
            component: App.Components.LitterProfile
        },
        '/users': {
            component: App.Components.Users
        },
        '/users/:userId/edit': {
            name: 'userEdit',
            component: App.Components.User
        },
        '/users/create': {
            component: App.Components.User
        },
        '/schedule': {
            component: App.Components.Schedule
        },
        '/schedule/:filter': {
            name: 'schedule',
            component: App.Components.Schedule
        },
        '/settings': {
            component: App.Components.Settings
        },
        'account': {
            component: App.Components.AccountSettings
        },
        '/ledger': {
            component: App.Components.Ledger
        },
        '/ledger/categories': {
            component: App.Components.LedgerCategories
        },
        '/ledger/:filter': {
            component: App.Components.Ledger
        },
        '/reports': {
            component: App.Components.Reports
        },
        '/wizard': {
            component: App.Components.Wizard,
            subRoutes: {
                '/settings': {
                    component: App.Components.WizardSettings
                },
                '/breeders': {
                    component: App.Components.WizardBreeders
                },
                '/ledgers': {
                	component: App.Components.WizardLedgers
                }
            }
        },
        '/cage-cards/templates': {
            component: App.Components.CageCardsTemplatesList
        }
    });
    router.start({
        components: {
            'broadcast-form': App.Components.BroadcastForm,
            'broadcast-alert': App.Components.BroadcastAlert
        }
    }, '#vue-app');

    if (window.user_id) {
        // Only invoke for authenticated users
        window.NotificationManager.updateServer();
    }
};
