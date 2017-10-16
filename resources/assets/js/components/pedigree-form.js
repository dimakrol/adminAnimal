App.Components.PedigreeForm = {
    template: "#pedigree-form-template",
    data: function () {
        return {
            bucks: [],
            does: [],
            newBuck: {
                sex: "buck"
            },
            newDoe: {
                sex: "doe"
            },
            errors: {},
            warnings: {},
            options: [
            ],
            rabbit: '',
            loading: 0,
            checked: false
        }
    },
    created: function() {
        this.fetchBreeders();
    },
    props: [ 'breeder','breeders'],
    components: {
        'image-upload': App.Components.ImageUpload,
        'lbs-oz-input': App.Components.LbsOzInput
    },
    computed: {
        weight_unit: function () {
            if (this.breeder.breeder) {
                return this.breeder.breeder.weight_unit;
            } else if (this.breeder.kit) {
                return this.breeder.kit.weight_unit;
            }
        }
    },
    watch: {
        breeder: function () {
            $('.js_icheck-breeder-blue, .js_icheck-breeder-red').iCheck('update');
            $('#pedigree-prefix').typeahead('val', this.breeder.prefix);
            $('#pedigree-name').typeahead('val', this.breeder.name);
            $('#pedigree-color').typeahead('val', this.breeder.color);
            $('#pedigree-breed').typeahead('val', this.breeder.breed);
            $('#breederSelect').val(this.breeder.rabbit_breeders_id);

            this.checked = this.breeder.rabbit_breeders_id;
        },
        rabbit: function(val, oldVal) {
            var _this17 = this;
            if(val !== "")
            {
                _this17.$http.get('/admin/fetchBreeder/' + val, function (resp) { // remove the .data if not sent by server
                        // this.breeder = resp;
                        // if(val == 0)
                        _this17.breeder.prefix = resp.prefix;
                        _this17.breeder.name = resp.name;
                        _this17.breeder.custom_id = resp.tattoo;
                        _this17.breeder.color = resp.color;
                        _this17.breeder.sex = resp.sex;
                        _this17.breeder.breed = resp.breed;
                        _this17.breeder.day_of_birth = resp.date_of_birth;
                        _this17.breeder.aquired = resp.aquired;
                        _this17.breeder.weight = resp.weight;
                        _this17.breeder.registration_number = resp.registration_number;
                        _this17.breeder.champion_number = resp.champion_number;
                        _this17.breeder.legs = resp.legs;
                        _this17.breeder.image = resp.image;
                    }).bind(_this17);
            }
            else {
                // if(_this17.breeder.id != undefined)
                // {
                //     _this17.$http.get('/admin/pedigrees/' + _this17.breeder.id, function (resp) {
                //         _this17.breeder = resp;
                //     }).bind(_this17);
                // }
                _this17.checked = false;

            }
            // console.log(this.breeder);
        }
    },
    methods: {
        fetchBreeders: function fetchBreeders()
        {
            this.$http.get('/admin/fetchBreeders', function (resp) { // remove the .data if not sent by server
                    this.options = resp;
                }).bind(this);
        },
        uniqueFieldSet: function(field){
            return _.unique(_.pluck(_.flatten([].concat(this.does, this.bucks)), field));
        },

        initModal: function () {
            var _this = this;
            //App.initDatePicker();
            api.getBreedersList().then(function (breeders) {
                _this.bucks = breeders.bucks;
                _this.does = breeders.does;
                var breeds = _this.uniqueFieldSet("breed");
                var names = _this.uniqueFieldSet("name");
                var colors = _this.uniqueFieldSet("color");
                var prefix = _this.uniqueFieldSet("prefix");

                $('#pedigree-prefix').typeahead({
                    hint: true,
                    highlight: true,
                    minLength: 0
                }, {
                    source: _this.substringMatcher(prefix)
                });
                
                $('#pedigree-breed').typeahead({
                    hint: true,
                    highlight: true,
                    minLength: 0
                }, {
                    source: _this.substringMatcher(breeds)
                });

                $('#pedigree-name').typeahead({
                    hint: true,
                    highlight: true,
                    minLength: 0
                }, {
                    source: _this.substringMatcher(names)
                });

                $('#pedigree-color').typeahead({
                    hint: true,
                    highlight: true,
                    minLength: 0
                }, {
                    source: _this.substringMatcher(colors)
                });
            });
        },

        sendBreeder: function () {
            var _this2 = this;
            this.loading = 1;
            this.breeder.weight_date = Date.now();
            var breeder = this.breeder;
            var rabbit_breeders_id;
            rabbit_breeders_id = $('#breederSelect option:selected').val();

            if(!this.checked)
                rabbit_breeders_id = null;
            breeder.rabbit_breeders_id = rabbit_breeders_id;

            api.savePedigree(breeder).then(function (data) {
                _this2.loading = 0;
                _this2.rabbit = 0;
                $('#pedigree-form').modal('hide');
                if (breeder.id == 0) {
                   _this2.breeders.push(data);
                } else {
                    var match = _.find(_this2.breeders, function (item) {
                       return item.id === breeder.id;
                    });
                    if (match) {
                       _.extendOwn(match, data);
                    }
                    _this2.breeder = data;
                }
               //this.closeModal();
            }, function (response) {
                _this2.loading = 0;
                _this2.errors = response.errors;
            });
        },

        checkDoubledId: function () {
            api.checkBreederId(this.breeder.tattoo).then(check => {
                if(check.idDoubled) {
                    this.warnings = { tattoo: ['Breeder ID is duplicated'] };
                } else {
                    this.warnings = {};
                }
            });
        },


        addNewBuck: function () {
            var _this4 = this;

            this.loading = 1;
            api.saveBreeder(_.extend({}, App.emptyBreeder, this.newBuck)).then(function (data) {
                _this4.loading = 0;
                if (data.id) {
                    _this4.breeder.prefix = '';
                    _this4.bucks.push(data);
                    _this4.breeder.father_id = data.id;
                    _this4.newBuck = { sex: "buck" };
                }
            });
        },

        addNewDoe: function () {
            var _this5 = this;

            this.loading = 1;
            api.saveBreeder($.extend({}, App.emptyBreeder, this.newDoe)).then(function (data) {
                _this5.loading = 0;
                if (data.id) {
                    _this5.does.push(data);
                    _this5.breeder.mother_id = data.id;
                    _this5.newDoe = { sex: "doe" };
                }
            });
        },
        substringMatcher: function(strs) {
            return function findMatches(q, cb) {
                var matches, substrRegex;

                // an array that will be populated with substring matches
                matches = [];

                // regex used to determine if a string contains the substring `q`
                substrRegex = new RegExp(q, 'i');

                // iterate through the pool of strings and for any string that
                // contains the substring `q`, add it to the `matches` array
                $.each(strs, function(i, str) {
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
        $('.js_icheck-breeder-blue').iCheck({
            checkboxClass: 'icheckbox_square-blue',
            radioClass: 'iradio_square-blue'
        }).on('ifChecked', function (event) {
            this.breeder.sex = "buck";
        }.bind(this));
        //Red color scheme for iCheck
        $('.js_icheck-breeder-red').iCheck({
            checkboxClass: 'icheckbox_square-red',
            radioClass: 'iradio_square-red'
        }).on('ifChecked', function (event) {
            this.breeder.sex = "doe";
        }.bind(this));
    },
    events: {
        pedigree_form_refresh: function () {
            this.initModal();
        }
    }

};
