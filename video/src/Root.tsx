import { Composition } from "remotion";
import { BondiFilm, FILM_FRAMES, FPS } from "./Film";
import { Constellation, CONSTELLATION_FPS, CONSTELLATION_FRAMES } from "./Constellation";

export const Root = () => (
  <>
    <Composition id="BondiFilm" component={BondiFilm} durationInFrames={FILM_FRAMES} fps={FPS} width={1920} height={1080} />
    <Composition id="Constellation" component={Constellation} durationInFrames={CONSTELLATION_FRAMES} fps={CONSTELLATION_FPS}
                 width={1920} height={1080} />
  </>
);
