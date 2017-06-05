import { Component } from '@angular/core';
import { NavController, NavParams } from 'ionic-angular';
import { CommentComponent } from '../../components/card/comment.component';


@Component({
  selector: 'new-card-page',
  templateUrl: 'new.html'
})
export class NewCardPage {
  item: any;
  industries: string[];

  constructor(public navCtrl: NavController, public navParams: NavParams) {
    // If we navigated to this page, we will have an item available as a nav param
    this.industries = ['Agriculture', 'Art', 'Architecture', 'Business', 'Computer Science', 'Design', 'Education', 'Engineering', 'Finance', 'Healthcare', 'Legal', 'Marketing', 'Music', 'Policy', 'Science', 'Writing'];
    this.item = {
    	industry: 0,
    	pitch: '',
    	location: true,
    	stage: 0
    };
  }
}
