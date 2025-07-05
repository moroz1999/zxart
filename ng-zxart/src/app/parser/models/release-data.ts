import {ReleaseAuthorData} from './release-author-data';

export interface ReleaseData {
    id: number,
    title: string,
    url: string,
    year: number,
    authors: ReleaseAuthorData[],
}
