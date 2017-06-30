import { Component } from '@angular/core';
import { NavController, NavParams } from 'ionic-angular';
import * as globs from '../../app/globals'


@Component({
  selector: 'new-card-page',
  templateUrl: 'new.html'
})
export class NewCardPage {
  item: any;
  // industries: string[] = ['Agriculture', 'Art', 'Architecture', 'Business', 'Computer Science', 'Design', 'Education', 'Engineering', 'Entrepreneurship', 'Finance', 'Government', 'Healthcare', 'Humanities', 'Journalism', 'Languages', 'Law', 'Lifestyle', 'Marketing', 'Math', 'Music', 'Performing Arts', 'Policy Planning', 'Science', 'Social Impact', 'Sports', 'Writing'];
  // stages: string[] = ['Ideation', 'User Discovery', 'Design', 'Execution', 'Marketing', 'Growth'];
  private industries = globs.INDUSTRIES;
  private challenges = globs.SKILLS;
  private stages = globs.STAGES;
  private networks = globs.NETWORKS;

  constructor(public navCtrl: NavController, public navParams: NavParams) {
    // If we navigated to this page, we will have an item available as a nav param    
    this.item = {
    	industry: null,
    	pitch: '',
      who: '',
      location: true,
    	anonymous: false,
      challenge_text: '',
      challenge_index: null,
    	stage: 0,
      networks: 0,
    };
  }

  shareTapped(){
    alert('Share!');
  }
}
