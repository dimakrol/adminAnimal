<template id="wizard__settings-template" xmlns="http://www.w3.org/1999/html" xmlns:v-on="http://www.w3.org/1999/xhtml">

    <div id="wizard__settings">

        <div class="col-md-8 col-md-offset-2 col-lg-6 col-lg-offset-3">
            <div class="box box-solid bg-green-gradient">

                <!-- /.box-header -->
                <div class="box-body"><h2 class="text-center"><img src="img/logo-tiny.png"><br>Welcome to Hutch!</h2>

                    <h4 class="text-center">Let's start by setting up your account</h4>
                    <p class="text-center">Enter these basic settings to customize Hutch to your rabbitry<br></p>
                    <a href="#" class="btn btn-success btn-sm pull-right" @click.prevent="skipSettings()">
                        Skip <i class="fa fa-chevron-right"></i>
                    </a>
                </div>
                <!-- /.box-body -->
            </div>
        </div>

        <div class="row" v-show="step==1">
            <div class="col-md-8 col-md-offset-2 col-lg-6 col-lg-offset-3">
                <div class="box box-solid box-primary">
                    <div class="box-header text-center"><h3 class="box-title"><i class="fa fa-calendar-check-o"></i> Customize Your Breed Chain</h3></div>
                    <div class="box-body">
                        <div class="row"><div class="col-md-4"><p>When you schedule a breed, Hutch adds a chain of events to your schedule.  </p>
                                <p>Space these events by editing the amount of days from the breed date.</p>
                                <p>Events before "Kindle/birth" will be added to the schedule when the breeding is created. Events after the birth date will be added to the schedule when the Birth is reported.</p>
                                <p>Change the name of events or add new tasks to this chain to customize it to your needs. </p>

                                <p>To add a task to the chain, click the "Add New Event" button at the bottom, and delete an event by clicking the trash can to the right of the event.</p></div>

                            <div class="col-sm-8">

                                <div class="tab-pane active" id="timeline">

                                    <!-- The timeline -->
                                    <ul class="timeline timeline-inverse">

                                        <li v-for="chain in chains" v-bind:class="chain.id">
                                            <i v-bind:class="chain.icon" class="fa"></i>
                                            <div class="timeline-item">
                                                <button v-if="['fa-venus-mars bg-blue', 'fa-venus-mars bg-blue original', 'fa-birthday-cake bg-green', 'fa-birthday-cake bg-green original', 'fa-balance-scale bg-yellow first-weight'].indexOf(chain.icon) === -1" type="button" class="btn btn-default btn-sm pull-right" style="margin-right: -33px; margin-top: 9px;"  @click.prevent="removeChain(chain.id)"><i class="fa fa-trash"></i></button>
                                                <input type="hidden" v-model="user.breedchains.icon[chain.id]" value="@{{ chain.icon }}">
                                                <span class="time"><input size="2" type="text" placeholder="0" value="@{{ chain.days }}" v-model="user.breedchains.days[chain.id]"> Days</span>
                                                <h3 class="timeline-header"><input placeholder="Breed" type="text" value="@{{ chain.name }}" v-model="user.breedchains.name[chain.id]"></h3>
                                            </div>
                                        </li>

                                    </ul>
                                </div><button class="btn btn-submit btn-default btn-lg" data-toggle="modal" href="#new_chain"><i class="fa fa-plus"></i> Add New Event</button>
                            </div>

                        </div>

                    </div>

                </div>
            </div>
        </div>

        <div class="row" v-show="step==2">
            <div class="col-md-8 col-md-offset-2 col-lg-6 col-lg-offset-3">
                <div class="box box-solid box-primary">
                    <div class="box-header text-center"><h3 class="box-title"><!-- <i class="fa fa-balance-scale"></i> --> <!-- Select Weight Units --> General Settings </h3></div>
                    <div class="box-body">
                        <div class="col-md-3"></div>

                        <div class="col-md-6"><h4 class="text-center">Weight Units</h4>
                        <p>Select the units to determine how rabbit weights are displayed throughout the system. </p>
                            <div class="radio">
                                <label>
                                    <input name="weight_units" value="Ounces" type="radio" v-model="user.general_weight_units">
                                    Ounces as <strong>1.2 oz</strong>
                                </label>
                            </div>
                            <div class="radio">
                                <label>
                                    <input name="weight_units" value="Pounds" checked="" type="radio" v-model="user.general_weight_units">Pounds as <strong>3.6 lbs</strong>
                                </label>
                            </div>
                            <div class="radio">
                                <label>
                                    <input name="weight_units" value="Pound/Ounces" type="radio" v-model="user.general_weight_units">Pounds/Ounces as <strong>10 lbs 2 oz</strong>
                                </label>
                            </div>
                            <div class="radio">
                                <label>
                                    <input name="weight_units" value="Grams" type="radio" v-model="user.general_weight_units">Grams as <strong>25.5 g</strong>
                                </label>
                            </div>
                            <div class="radio">
                                <label>
                                    <input name="weight_units" value="Kilograms" type="radio" v-model="user.general_weight_units">Kilograms as <strong>2.8 kg</strong>
                                </label>
                            </div>
                            
                            <br>
                            <h4 class="text-center">Date Format</h4>
                            <p>Select the format for displaying your dates.</p>
                                <select id="date-format" name="user_date_format" class="form-control"
                                        v-model="user.date_format">
                                    <option value="US">US mm/dd/yyyy</option>
                                    <option value="INT">International dd/mm/yyyy</option>
                                </select>
                             <br>
                            </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row" v-show="step==3">
            <div class="col-md-8 col-md-offset-2 col-lg-6 col-lg-offset-3">
                <div class="box box-solid box-primary">
                    <div class="box-header text-center"><h3 class="box-title"><i class="fa fa-share-alt"></i> Pedigree
                            Settings</h3></div>
                    <div class="box-body">
                    <input type="hidden" name="units" v-model="user.pedigree_number_generations">
                       
                        <!-- <div class="row">
                            <div class="col-md-6"><p>Select the number of generations you want to display on your
                                    pedigrees. This will determine the layout of your web and pdf pedigrees generated by
                                    Hutch.</p>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="" class="">Number of Generations</label>
                                    <select class="form-control input-lg" name="units" v-model="user.pedigree_number_generations">
                                        <option>2</option>
                                        <option>3</option>
                                        <option>4</option>
                                    </select>
                                </div>
                            </div>

                        </div>
                        <hr> -->
                       
                        <div class="row">
                            <div class="col-md-6"><p>Rabbitry Information is displayed at the top of your pedigrees.
                                    This should include the name, address, and contact information (phone, web adrees,
                                    email) of your Rabbitry.</p>


                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="">Rabbitry Information</label>
                                    <textarea placeholder="Rabbitry name, address, and contact information" rows="3"
                                              class="form-control" v-model="user.pedigree_rabbitry_information">
                                    </textarea>
                                </div>
                            </div>

                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-6"><p>Upload a logo to be included on your pedigrees above your rabbitry
                                    information. The logo should be gif, jpg, or png, and should be no more than 300
                                    pixels wide and 100 pixels tall (300px X 100px).</p>


                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <settings-image-upload :image.sync="user.pedigree_logo"></settings-image-upload>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-6"><p>Add default prefix to be shown for litters and kits born in your rabbitry.</p>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="">Default Prefix</label>
                                    <input class="form-control" v-model="user.default_prefix" />
                                </div>
                            </div>

                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
		
		<div class="row" v-show="step==4">
            <div class="col-md-8 col-md-offset-2 col-lg-6 col-lg-offset-3">
				
				<div class="box box-solid box-primary">
                    <div class="box-header text-center"><h3 class="box-title">Notification Settings</h3></div>
                    <div class="box-body">
                    	<div class="col-md-3"></div>

                        <div class="col-md-6">
                        <p class="text-muted" v-if="!supports_notifications">
                              Your browser or OS does not support notifications. Please install the latest Chrome, Firefox or Opera browser if you want to receive instant notifications.
                        </p>
                        <template v-else>
                            <div class="alert alert-success" v-if="success_notifications.success && success_notifications.success[0]">
                                <i class="fa fa-check"></i> Settings Updated
                            </div>

                            <div class="form-group">
                                <label>Receive notification in this browser</label>
                                <template v-if="notifications_disabled">
                                    <p class="form-control-static text-danger">
                                          You have denied the notification permission. Please change your settings if you wish to receive notifications.
                                    </p>
                                </template>
                                <template v-else>
                                    <template v-if="notifications_subscribed">
                                        <p class="form-control-static text-success">Notifications are enabled</p>
                                        <button type="button" class="btn btn-danger" @click="disableNotifications">Disable</button>
                                        <button type="button" class="pull-right btn btn-primary" @click="sendTestNotification">
                                            Send test notification
                                        </button>
                                    </template>
                                    <template v-else>
                                        <p class="form-control-static text-danger">Notifications are not enabled</p>
                                        <button type="button" class="btn btn-success" @click="subscribeToNotifications">Enable</button>
                                    </template>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
        </div>
        <div class="row" v-show="step==5">
            <div class="col-md-8 col-md-offset-2 col-lg-6 col-lg-offset-3">
                
                <div class="box box-solid box-primary">
                    <div class="box-header text-center"><h3 class="box-title">Home Screen</h3></div>
                    <div class="box-body">
                    	<div class="col-md-3"></div>
                        <div class="col-md-6">
                          <p>Add Hutch to your home screen to load like any other app. This makes it faster and easier to log in and use Hutch in the future.</p>
                        <script>
                            var addtohome = addToHomescreen({
                                lifespan : 0, 
                                maxDisplayCount : 0,
                                autostart: false
                            });
                        </script>
                          <p class="text-center"><a onclick="addtohome.show();" href="javascript:void(0);" class="btn btn-success btn-lg">
                             Add Hutch to my Home Screen
                          </a></p><br />

                    </div>
                </div>
            </div>
        </div>
        </div>

        <div class="row text-center">
            <button type="button" class="btn btn-success btn-lg" @click.prevent="updateSettings()" v-show="step!=5">
                <i v-if="loading" class="fa fa-spinner fa-pulse fa-fw"></i>
                <span v-if="!loading">
                    Save Settings <i class="fa fa-arrow-circle-right"></i>
                </span>

            </button>
            <br><br>
            <button type="button" class="btn btn-default btn-sm" @click.prevent="skipSettings()" href="#" v-if="step!=5"> Skip <i class="fa fa-chevron-right"></i> </button>
            <button type="button" class="btn btn-default btn-sm" @click.prevent="skipSettings()" href="#" v-if="step==5"> Next <i class="fa fa-chevron-right"></i> </button>
            <br><br><br><br>
        </div>

        @include('components/_chain-modal-settings')

    </div>
</template>
<!-- <script type="text/javascript" src="js/addtohomescreen.min.js"></script> -->