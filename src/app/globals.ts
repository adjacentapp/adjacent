'use strict';

let DEV = true;
DEV = false;

let localURL 	= "http://localhost/~salsaia/adjacent/api/v2/";
let remoteURL 	= "http://adjacent.wuex59etyj.us-west-2.elasticbeanstalk.com/api/v2/";

export const BASE_API_URL = DEV ? localURL : remoteURL;

export const INDUSTRIES = ['Agriculture', 'Art', 'Architecture', 'Business', 'Computer Science', 'Design', 'Education', 'Engineering', /*'Entrepreneurship',*/ 'Finance', 'Government', 'Healthcare', 'Humanities', 'Journalism', 'Languages', 'Law', 'Marketing', 'Math', 'Music', 'Performing Arts', 'Science', 'Social Impact', 'Sports', 'Writing'];

export let firstFollow = true;
export let setFirstFollowFalse = () => {
	firstFollow = false;
};