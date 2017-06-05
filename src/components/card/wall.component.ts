import { Component, Input } from '@angular/core';
import { Http } from '@angular/http'
import 'rxjs/add/operator/map';

@Component({
  selector: 'wall-component',
  template: '<comment-component *ngFor="let comment of comments" [item]="comment"></comment-component>'
})
export class WallComponent {
  @Input() card_id: string;
  comments: any[];

  constructor(public http: Http) {}

  ngOnInit() {
  	var DEV = true;
  	var prod_url = "http://adjacent.wuex59etyj.us-west-2.elasticbeanstalk.com/api/v1/";
  	var dev_url = "http://localhost/~salsaia/adjacent/api/v2/";
  	var base_url = DEV ? dev_url : prod_url;
  	var query = '?card_id=' + this.card_id;
  	var url = base_url + 'get_card_wall.php' + query;
    console.log(url);
  	
  	this.http.get(url).map(res => res.json()).subscribe(data => {
  		this.comments = data;
  		console.log(data);
  	});
  }
}
