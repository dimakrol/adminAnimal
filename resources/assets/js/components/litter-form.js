App.Components.LitterForm = {
    template: "#litter-form-template",
    data: function () {
        return {
            bucks: [],
            does: [],
            errors: {},
            loading: 1,
            newBuck: {
                sex: "buck"
            },
            newDoe: {
                sex: "doe"
            },
            butchered: false
        }
    },
    props: ['litter','litters'],

    watch: {
        litter: function(value) {
            if (!value) {
                return;
            }
            this.setFilterBreeders(value.mother_id, value.father_id);
            this.resetArchivedFilterBreeders();
            this.butchered = value.butchered;
			$('#litter-prefix').typeahead('val', this.litter.prefix);
        },

        'litter.mother_id': function(){
            if (this.litter) {
                this.setFilterBreeders(this.litter.mother_id, this.litter.father_id);
            }
        },

        'litter.father_id': function(){
            if (this.litter) {
                this.setFilterBreeders(this.litter.mother_id, this.litter.father_id);
            }
        },
        'butchered': function(newValue, oldValue){
            $(this.$els.litter_butchered).iCheck('update');
        }
    },
    methods: {
        initModal: function () {
            var _this = this;
            _this.loading = 1;
            api.getBreedersList().then(breeders => {
                _this.loading = 0;
                _this.bucks = breeders.bucks;
                _this.does = breeders.does;
            });
			
			api.getLittersList().then(function (litters) {
                // _this.loading = 0;
				var prefixes = $.map(litters, function(litter) {
					return litter.prefix;
				});
				
				$('#litter-prefix').typeahead({
                    hint: true,
                    highlight: true,
                    minLength: 0
                }, {
                    source: _this.substringMatcher(prefixes)
                });
            });

            $(this.$els.litter_butchered).iCheck({
                checkboxClass: 'icheckbox_square-blue',
                radioClass: 'iradio_square-blue',
                increaseArea: '20%' // optional
            }).on('ifChecked', function(event){
                this.butchered = true;
            }.bind(this))
                .on('ifUnchecked', function(event){
                    this.butchered = false;
                }.bind(this));
        },

        sendLitter: function () {
            this.loading = 1;
            var litter = this.litter;
            litter.animal_type='rabbit';
            litter.butchered = this.butchered;
            if(litter.id != 0) {
                litter._method = "PUT";
            }

            //if(!litter.mother_id){
            //    litter.mother_id = null;
            //}
            //
            //if(!litter.father_id){
            //    litter.father_id = null;
            //}


            api.saveLitter(litter).then(
                data => {
                    this.loading = 0;
                    $('#litter-form').modal('hide');
                    if (litter.id == 0) {
                        data.archived = 0;
                        this.litters.push(data);
                    } else {
                        this.litter = data;
                        this.litter.butchered_date = moment(this.litter.butchered_at, 'YYYY-MM-DD H:i:s')
                                                        .format(App.dateFormat);
                    }
                    this.$dispatch('litter-updated', this.litter.id);
                    //this.closeModal();
                },
                response => {
                    this.loading = 0;
                    this.errors = response.errors;
                }
            );
        },

        addNewBuck: function () {
            this.loading = 1;
            api.saveBreeder(_.extend({}, App.emptyBreeder, this.newBuck)).then(data => {
                this.loading = 0;
                if (data.id) {
                    this.bucks.push(data);
                    this.litter.father_id = data.id;
                    this.newBuck = { sex: "buck" };
                }
            });
        },

        addNewDoe: function () {
            this.loading = 1;
            api.saveBreeder($.extend({}, App.emptyBreeder, this.newDoe)).then(data => {
                this.loading = 0;
                if (data.id) {
                    this.does.push(data);
                    this.litter.mother_id = data.id;
                    this.newDoe = { sex: "doe" };
                }
            });
        },
		
		substringMatcher: function substringMatcher(strs) {
		
            return function findMatches(q, cb) {
                var matches, substrRegex;

                // an array that will be populated with substring matches
                matches = [];

                // regex used to determine if a string contains the substring `q`
                substrRegex = new RegExp(q, 'i');

                // iterate through the pool of strings and for any string that
                // contains the substring `q`, add it to the `matches` array
                $.each(strs, function (i, str) {
                    if (substrRegex.test(str)) {
                        matches.push(str);
                    }
                });

                cb(matches);
            };
        }

    },
    ready: function () {
        this.initModal();
    },

    mixins: [App.Mixins.BreedersFilter]

};
