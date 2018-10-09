import { Component, Input } from '@angular/core';
import { NavController, NavParams, ToastController } from 'ionic-angular';
import { CardProvider, Card } from '../../providers/card/card';
import { ShowMessagePage } from '../../pages/messages/show';
import { Conversation } from '../../providers/messages/messages';
// import { FollowersPage } from '../../pages/followers/followers';
import { AuthProvider } from '../../providers/auth/auth';
import { SocialSharing } from '@ionic-native/social-sharing';
import * as globs from '../../app/globals'

@Component({
  selector: 'idea-card',
  templateUrl: 'card.html'
})
export class CardComponent {
  @Input() item: Card;
  @Input() showTopComment: boolean = true;
  @Input() showTopCommentVotes: boolean = true;
  @Input() showDetails: boolean = false;
  @Input() showReply: boolean = true;
  @Input() hideButtons: boolean = false;
  founder: boolean = false;

  constructor(
    public navCtrl: NavController, 
    public navParams: NavParams, 
    private toastCtrl: ToastController, 
    private card: CardProvider, 
    private auth: AuthProvider,
    private socialSharing: SocialSharing
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
    if(!item.followers.length)
      return;
    this.navCtrl.push('FollowersPage', {
      card_id: item.id
    });
  }

  goToMessage(e){
    e.stopPropagation();
    this.navCtrl.push(ShowMessagePage, {
      item: new Conversation(
        null, // id
        {id: this.item.founder_id}, // other
        {...this.item}, // card
        [], // messages
        null, // timstamp
        null // unread
      )
    });
  }

  shareTapped(e, item){
    console.log("http://" + globs.SHARE_URL + "/?idea=" + item.id);
    e.stopPropagation();
    let data = {
      user_id: this.auth.currentUser.id,
      card_id: item.id
    };
    this.card.share(data).subscribe(
      success => console.log(success),
      error => console.log(error)
    );
    this.socialSharing.share(
      "Check out this idea I found in Adjacent -- " + item.pitch, // message
      "Shared from Adjacent",   // subject
      "www/assets/img/industries/" + item.industry.replace(" ","_") + ".jpg", // file
      // "adjacentapp://app/idea/" + item.id  //url
      "http://" + globs.SHARE_URL + "/?idea=" + item.id  //url
     ); 
  }

}
