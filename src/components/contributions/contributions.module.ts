import { NgModule } from '@angular/core';
import { IonicPageModule } from 'ionic-angular';
import { ContributionsComponent } from './contributions';

@NgModule({
  declarations: [
    ContributionsComponent,
  ],
  imports: [
    IonicPageModule.forChild(ContributionsComponent),
  ],
  exports: [
    ContributionsComponent
  ]
})
export class ContributionsComponentModule {}
