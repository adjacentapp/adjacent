import { Component, Input } from '@angular/core';
import { NavController, NavParams, ToastController } from 'ionic-angular';
import { CardService } from '../../services/card.service';
import { SocialSharing } from '@ionic-native/social-sharing';

import * as globs from '../../app/globals'

@Component({
  selector: 'card-component',
  templateUrl: 'card-component.html'
})
export class CardComponent {
  @Input() item: {
    id: number,
    founder_id: number,
    industry: number,
    pitch: string,
    distance: string,
    following: boolean,
    comments: any,
    topComment: any,
  };
  @Input() handleTap: any;
  @Input() showTopComment: boolean = true;
  @Input() showVotes: boolean = true;
  founder: boolean = false;
  industries: string[] = ['Agriculture', 'Art', 'Architecture', 'Business', 'Computer Science', 'Design', 'Education', 'Engineering', 'Entrepreneurship', 'Finance', 'Government', 'Healthcare', 'Humanities', 'Journalism', 'Languages', 'Law', 'Lifestyle', 'Marketing', 'Math', 'Music', 'Performing Arts', 'Policy Planning', 'Science', 'Social Impact', 'Sports', 'Writing'];

  constructor(public navCtrl: NavController, public navParams: NavParams, private toastCtrl: ToastController, private cardService: CardService, private socialSharing: SocialSharing) {}

  ngOnInit() {
    // item is now accessible
    if(this.item.founder_id == 1) this.founder = true;
  }

  itemTapped(event, item) {
    if(this.handleTap)
      this.handleTap(event, item);
  }

  followTapped(e, item){
    e.stopPropagation();
    let data = {
      user_id: 1,
      card_id: item.id,
      new_entry: !item.following
    };
    this.cardService.follow(data).subscribe(
      success => console.log(success),
      error => console.log(error)
    );
    // dangerous optimism!
    item.following = !item.following;

    if(globs.firstFollow){
      globs.setFirstFollowFalse();
      this.firstFollowToast();
    }
  }

  firstFollowToast() {
    let toast = this.toastCtrl.create({
      message: 'You will be notified when the founder iterates on this idea.',
      duration: 6000,
      position: 'bottom',
      showCloseButton: true,
    });
    toast.present();
  }

  shareTapped(e, item){
    e.stopPropagation();
    this.socialSharing.share(
      "Check out this idea I found in the Adjacent!", // message
      null,   // subject
      "www/assets/img/anon_photo.png", // file
      null  //url
     ); 
  }

}
