import { Component, Input } from '@angular/core';
import { NavController, NavParams, ToastController } from 'ionic-angular';
import { CardProvider, Card } from '../../providers/card/card';
import { FollowersPage } from '../../pages/followers/followers';
import { SocialSharing } from '@ionic-native/social-sharing';
import { AuthProvider } from '../../providers/auth/auth';
import * as globs from '../../app/globals'

@Component({
  selector: 'card-component',
  templateUrl: 'card-component.html'
})
export class CardComponent {
  @Input() item: Card;
  @Input() showTopComment: boolean = true;
  @Input() showTopCommentVotes: boolean = true;
  @Input() showDetails: boolean = false;
  founder: boolean = false;
  // industries: string[] = globs.INDUSTRIES;
  stages: string[] = globs.STAGES;

  constructor(
    public navCtrl: NavController, 
    public navParams: NavParams, 
    private toastCtrl: ToastController, 
    private card: CardProvider, 
    private socialSharing: SocialSharing,
    private auth: AuthProvider
  ) {}

  ngOnInit() {
    // item is now accessible
    if(this.item.founder_id == this.auth.currentUser.id) this.founder = true;
  }

  newTopComment(item){
    // for new-comment shortcut: set newComment as topComment
    this.item.topComment = item;
  }

  followTapped(e, item){
    e.stopPropagation();
    let data = {
      user_id: this.auth.currentUser.id,
      card_id: item.id,
      new_entry: !item.following
    };
    this.card.follow(data).subscribe(
      success => console.log(success),
      error => console.log(error)
    );
    // dangerous optimism!
    item.following = !item.following;
    if(item.following)
      item.followers.push(this.auth.currentUser.id);
    else
      item.followers.splice(  item.followers.indexOf(this.auth.currentUser.id), 1 );
  }

  goToFollowers(e, item){
    e.stopPropagation();
    this.navCtrl.push(FollowersPage, {
      card_id: item.id
    });
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
