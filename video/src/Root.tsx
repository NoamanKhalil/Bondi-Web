import { Composition } from "remotion";
import { BondiFilm, FILM_FRAMES, FPS } from "./Film";

export const Root = () => (
  <Composition id="BondiFilm" component={BondiFilm} durationInFrames={FILM_FRAMES} fps={FPS} width={1920} height={1080} />
);
